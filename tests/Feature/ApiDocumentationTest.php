<?php

test('the api documentation is available', function () {
    $this->artisan('l5-swagger:generate')->assertExitCode(0);

    $this->get('/api/documentation')
        ->assertOk()
        ->assertSee('Documentation Center API');

    $this->get('/docs')
        ->assertOk()
        ->assertJsonPath('info.title', 'Documentation Center API')
        ->assertJsonPath('paths./api/v1/auth/signup.post.summary', 'Cadastra um novo usuário');
});
