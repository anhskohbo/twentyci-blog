<?php

namespace Tests;

use App\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function asAdmin()
    {
        $user = User::factory()->create(['level' => Group::ADMINISTRATOR]);

        $this->actingAs($user);

        return $user;
    }

    public function asEditor()
    {
        $user = User::factory()->create(['level' => Group::MEMBER]);

        $this->actingAs($user);

        return $user;
    }

    /**
     * Assert a top-level subset for an array.
     *
     * @param array $subset
     * @param array $array
     * @return void
     */
    public function assertSubset($subset, $array)
    {
        $values = collect($array)->only(array_keys($subset))->all();

        $this->assertEquals($subset, $values, 'The expected subset does not match the given array.');
    }
}
