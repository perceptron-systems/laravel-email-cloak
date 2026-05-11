@props([
    'email',
    'level' => null,
    'label' => null,
])

{!! app(\PerceptronSystems\EmailCloak\EmailCloak::class)->render($email, $level, $label) !!}
