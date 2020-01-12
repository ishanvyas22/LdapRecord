<?php

namespace LdapRecord\Tests\Query\Model;

use LdapRecord\Connection;
use LdapRecord\Models\Entry;
use LdapRecord\Models\Model;
use LdapRecord\Models\Scope;
use LdapRecord\Tests\TestCase;
use LdapRecord\Query\Model\Builder;

class BuilderScopeTest extends TestCase
{
    public function test_closure_scopes_can_be_applied()
    {
        $b = new Builder(new Connection);

        $b->withGlobalScope('foo', function ($query) use ($b) {
            $this->assertSame($b, $query);
        });

        $b->applyScopes();
    }

    public function test_class_scopes_can_be_applied()
    {
        $b = new Builder(new Connection);

        $b->setModel(new Entry);

        $b->withGlobalScope('foo', new TestModelScope);

        $b->applyScopes();

        $this->assertEquals('(foo=LdapRecord\Models\Entry)', $b->getUnescapedQuery());
    }

    public function test_scopes_can_be_removed_after_being_added()
    {
        $b = new Builder(new Connection);

        $b->withGlobalScope('foo', function () {});

        $b->withoutGlobalScope('foo');

        $this->assertEquals(['foo'], $b->removedScopes());
    }

    public function test_many_scopes_can_be_removed_after_being_applied()
    {
        $b = new Builder(new Connection);

        $b->withGlobalScope('foo', function () {});
        $b->withGlobalScope('bar', function () {});

        $b->withoutGlobalScopes(['foo', 'bar']);

        $this->assertEquals(['foo', 'bar'], $b->removedScopes());
    }
}

class TestModelScope implements Scope
{
    public function apply(Builder $query, Model $model)
    {
        $query->where('foo', '=', get_class($model));
    }
}
