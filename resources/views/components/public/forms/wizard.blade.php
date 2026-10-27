@props([
    'form',
    'steps' => [],
    'initialStep' => 0,
    'sectionCount' => 0,
    'hasResponse' => false,
])

<form
    method="POST"
    action="{{ URL::signedRoute('public.forms.submit', ['form' => $form->getKey()]) }}"
    enctype="multipart/form-data"
    {{ $attributes->class(['space-y-4']) }}
    data-form-progress-form
    data-form-steps
    data-form-initial-step="{{ $initialStep }}"
    data-form-autosave-url="{{ URL::signedRoute('public.forms.autosave', ['form' => $form->getKey()]) }}"
    data-form-image-delete-url="{{ URL::signedRoute('public.forms.images.destroy', ['form' => $form->getKey()]) }}"
    data-form-file-delete-url="{{ URL::signedRoute('public.forms.files.destroy', ['form' => $form->getKey()]) }}"
>
    @csrf

    <x-public.forms.step-nav :steps="$steps" :initial-step="$initialStep" :section-count="$sectionCount" />

    <p
        data-draft-autosave-status
        hidden
        role="status"
        aria-live="polite"
        class="px-1 text-xs font-medium text-gray-500 dark:text-gray-400"
    ></p>

    @foreach ($steps as $stepIndex => $step)
        <x-public.forms.step
            :step="$step"
            :index="$stepIndex"
            :total="$sectionCount"
            :initial-step="$initialStep"
        />
    @endforeach

    <x-public.forms.action-bar
        :initial-step="$initialStep"
        :section-count="$sectionCount"
        :has-response="$hasResponse"
    />
</form>
