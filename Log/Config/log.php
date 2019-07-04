<?php

return [

    'user' => [
        'model' => \App\Models\User::class,
        'primary_key' => 'id',
        'foreign_key' => 'user_id',
    ]
];