<?php

test('the public contact form stores a message', function () {
    $this->postJson('/api/v1/contact-messages', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'subject' => 'Question',
        'message' => 'Please contact me.',
    ])->assertCreated();

    $this->assertDatabaseHas('contact_messages', ['email' => 'jane@example.com']);
});
