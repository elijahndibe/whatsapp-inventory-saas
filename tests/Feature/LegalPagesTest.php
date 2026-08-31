<?php

namespace Tests\Feature;

use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    public function test_terms_page_renders(): void
    {
        $this->get(route('legal.terms'))->assertOk()->assertSee('Terms of Service');
    }

    public function test_privacy_page_renders(): void
    {
        $this->get(route('legal.privacy'))->assertOk()->assertSee('Privacy Policy');
    }
}
