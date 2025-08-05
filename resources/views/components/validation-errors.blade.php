@if ($errors->any())
    <div {{ $attributes->merge(['class' => 'bg-error/10 border border-error/30 rounded-lg p-4']) }}>
        <div class="flex items-start">
            <svg class="size-5 text-error mt-0.5 mr-3 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>

            <div>
                <div class="font-medium text-error text-lg">{{ __('¡Ups! Algo salió mal.') }}</div>

                <ul class="mt-2 space-y-1.5 pl-1 text-error/90">
                    @foreach ($errors->all() as $error)
                        <li class="flex items-start">
                            <span class="inline-block mr-2 text-error">•</span>
                            <span class="inline-block">{{ $error }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif
