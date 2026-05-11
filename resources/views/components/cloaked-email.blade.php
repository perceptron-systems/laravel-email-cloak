@props([
    'email',
    'level' => null,
    'label' => null,
])

{!! app(\Orsal\EmailCloak\EmailCloak::class)->render($email, $level, $label) !!}
