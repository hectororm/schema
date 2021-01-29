<?php
/*
 * This file is part of Hector ORM.
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2021 Ronan GIRON
 * @author    Ronan GIRON <https://github.com/ElGigi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code, to the root.
 */

declare(strict_types=1);

namespace Hector\Schema;

use ArrayIterator;
use Countable;
use Generator;
use Hector\Schema\Exception\NotFoundException;
use Hector\Schema\Exception\SchemaException;
use IteratorAggregate;
use ReflectionClass;
use ReflectionException;

/**
 * Class Table.
 *
 * @package Hector\Schema
 */
class Table implements Countable, IteratorAggregate
{
    public const TYPE_TABLE = 'table';
    public const TYPE_VIEW = 'view';

    private string $schema_name;
    private string $type;
    private string $name;
    private ?string $charset;
    private ?string $collation;
    /** @var Column[] */
    private array $columns = [];
    /** @var Index[] */
    private array $indexes = [];
    /** @var ForeignKey[] */
    private array $foreign_keys = [];
    private ?Schema $schema = null;

    /**
     * PHP serialize method.
     *
     * @return array
     */
    public function __serialize(): array
    {
        return [
            'schema_name' => $this->schema_name,
            'name' => $this->name,
            'type' => $this->type,
            'charset' => $this->charset,
            'collation' => $this->collation,
            'columns' => $this->columns,
            'indexes' => $this->indexes,
            'foreign_keys' => $this->foreign_keys,
        ];
    }

    /**
     * PHP unserialize method.
     *
     * @param array $data
     *
     * @throws ReflectionException
     */
    public function __unserialize(array $data): void
    {
        $this->schema_name = $data['schema_name'];
        $this->name = $data['name'];
        $this->type = $data['type'];
        $this->charset = $data['charset'];
        $this->collation = $data['collation'];
        $this->columns = $data['columns'];
        $this->indexes = $data['indexes'];
        $this->foreign_keys = $data['foreign_keys'];

        $this->restoreInheritance();
    }

    /**
     * Restore inheritance.
     *
     * @throws ReflectionException
     */
    public function restoreInheritance(): void
    {
        // Attach table to columns
        $reflectionClass = new ReflectionClass(Column::class);
        $reflectionProperty = $reflectionClass->getProperty('table');
        $reflectionProperty->setAccessible(true);
        foreach ($this->columns as $column) {
            $reflectionProperty->setValue($column, $this);
        }

        if ($this->type === static::TYPE_TABLE) {
            // Attach table to indexes
            $reflectionClass = new ReflectionClass(Index::class);
            $reflectionProperty = $reflectionClass->getProperty('table');
            $reflectionProperty->setAccessible(true);
            foreach ($this->indexes as $index) {
                $reflectionProperty->setValue($index, $this);
            }

            // Attach table to foreign keys
            $reflectionClass = new ReflectionClass(ForeignKey::class);
            $reflectionProperty = $reflectionClass->getProperty('table');
            $reflectionProperty->setAccessible(true);
            foreach ($this->foreign_keys as $foreign_key) {
                $reflectionProperty->setValue($foreign_key, $this);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        return count($this->columns);
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        return new ArrayIterator($this->columns);
    }

    /**
     * Get schema name.
     *
     * @param bool $quoted
     *
     * @return string
     */
    public function getSchemaName(bool $quoted = false): string
    {
        if ($quoted) {
            return sprintf('`%s`', $this->schema_name);
        }

        return $this->schema_name;
    }

    /**
     * Get name.
     *
     * @param bool $quoted
     *
     * @return string
     */
    public function getName(bool $quoted = false): string
    {
        if ($quoted) {
            return sprintf('`%s`', $this->name);
        }

        return $this->name;
    }

    /**
     * Get full name.
     *
     * @param bool $quoted
     *
     * @return string
     */
    public function getFullName(bool $quoted = false): string
    {
        return sprintf('%s.%s', $this->getSchemaName($quoted), $this->getName($quoted));
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get charset.
     *
     * @return string|null
     */
    public function getCharset(): ?string
    {
        return $this->charset;
    }

    /**
     * Get collation.
     *
     * @return string|null
     */
    public function getCollation(): ?string
    {
        return $this->collation;
    }

    /**
     * Get columns.
     *
     * @return Generator<Column>
     */
    public function getColumns(): Generator
    {
        yield from $this->columns;
    }

    /**
     * Get columns name.
     *
     * @param bool $quoted
     * @param string|null $tableAlias
     *
     * @return string[]
     */
    public function getColumnsName(bool $quoted = false, ?string $tableAlias = null): array
    {
        return array_values(array_map(fn(Column $column) => $column->getName($quoted, $tableAlias), $this->columns));
    }

    /**
     * Has column?
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasColumn(string $name): bool
    {
        return array_key_exists($name, $this->columns);
    }

    /**
     * Get column.
     *
     * @param string $name
     *
     * @return Column
     * @throws NotFoundException
     */
    public function getColumn(string $name): Column
    {
        return
            $this->columns[$name] ??
            throw new NotFoundException(sprintf('Column "%s" not found in table "%s"', $name, $this->name));
    }

    /**
     * Get auto increment column.
     *
     * @return Column|null
     */
    public function getAutoIncrementColumn(): ?Column
    {
        foreach ($this->getColumns() as $column) {
            if ($column->isAutoIncrement()) {
                return $column;
            }
        }

        return null;
    }

    /**
     * Get indexes.
     *
     * @param string|null $type
     *
     * @return Generator<Index>
     */
    public function getIndexes(?string $type = null): Generator
    {
        foreach ($this->indexes as $index) {
            if (null === $type || $type === $index->getType()) {
                yield $index;
            }
        }
    }

    /**
     * Has index?
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasIndex(string $name): bool
    {
        return array_key_exists($name, $this->indexes);
    }

    /**
     * Get index.
     *
     * @param string $name
     *
     * @return Index
     * @throws NotFoundException
     */
    public function getIndex(string $name): Index
    {
        return
            $this->indexes[$name] ??
            throw new NotFoundException(sprintf('Index "%s" not found in table "%s"', $name, $this->name));
    }

    /**
     * Get primary key.
     *
     * @return Index|null
     */
    public function getPrimaryIndex(): ?Index
    {
        $indexes = $this->getIndexes(Index::PRIMARY);

        return $indexes->current() ?? null;
    }

    /**
     * Get foreign keys.
     *
     * @param Table|null $table
     *
     * @return Generator<ForeignKey>
     * @throws SchemaException
     */
    public function getForeignKeys(Table $table = null): Generator
    {
        foreach ($this->foreign_keys as $foreign_key) {
            if (null === $table || $table === $foreign_key->getReferencedTable()) {
                yield $foreign_key;
            }
        }
    }

    /**
     * Get schema.
     *
     * @return Schema
     * @throws SchemaException
     */
    public function getSchema(): Schema
    {
        return $this->schema ?? throw new SchemaException('No schema attached to the table');
    }
}