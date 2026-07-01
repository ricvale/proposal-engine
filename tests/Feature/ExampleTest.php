<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * The root URL redirects to the proposals list.
     */
    public function test_the_application_redirects_to_proposals(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/proposals');
    }
}
