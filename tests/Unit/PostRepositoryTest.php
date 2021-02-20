<?php

namespace Tests\Unit;

use App\Repository\PostRepository;
use Tests\TestCase;

class PostRepositoryTest extends TestCase
{
    /** @var PostRepository */
    protected $repository;

    protected function setup(): void
    {
        parent::setUp();

        $this->repository = app(PostRepository::class);
    }

    public function test_query_without_an_user()
    {
        $query = $this->repository->query();

        $this->assertEquals('select * from `posts` where (`posts`.`status` = ?)', $query->toSql());
        $this->assertEquals(['publish'], $query->getBindings());
    }

    public function test_query_with_an_user()
    {
        $user = $this->asEditor();
        $query = $this->repository->query($user);

        $this->assertEquals(
            'select * from `posts` where (`posts`.`status` = ? or `posts`.`status` = ? and `posts`.`user_id` = ?)',
            $query->toSql()
        );

        $this->assertEquals(['publish', 'draft', $user->getKey()], $query->getBindings());
    }

    public function test_query_with_admin()
    {
        $user = $this->asAdmin();
        $query = $this->repository->query($user);

        $this->assertEquals(
            'select * from `posts` where (`posts`.`status` = ? or `posts`.`status` = ?)',
            $query->toSql()
        );

        $this->assertEquals(['publish', 'draft'], $query->getBindings());
    }

    public function test_build_index_method_with_search()
    {
        $query = $this->repository->query();

        $this->repository->buildIndexQuery($query, 'term');

        $this->assertEquals(
            'select * from `posts` where (`posts`.`status` = ?) and (`posts`.`id` like ? or `posts`.`title` like ? or `posts`.`slug` like ?) order by `posts`.`id` desc',
            $query->toSql()
        );

        $this->assertEquals(['publish', '%term%', '%term%', '%term%'], $query->getBindings());
    }

    public function test_build_index_method_with_search_by_id()
    {
        $query = $this->repository->query();

        $this->repository->buildIndexQuery($query, 100);

        $this->assertEquals(
            'select * from `posts` where (`posts`.`status` = ?) and (`posts`.`id` = ? or `posts`.`id` like ? or `posts`.`title` like ? or `posts`.`slug` like ?) order by `posts`.`id` desc',
            $query->toSql()
        );

        $this->assertEquals(['publish', '100', '%100%', '%100%', '%100%'], $query->getBindings());
    }
}
