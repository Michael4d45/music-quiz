<?php

declare(strict_types=1);

it('displays home page', function (): void {
    // Visit home page
    visit(route('home'))
        ->assertNoJavaScriptErrors();
});
