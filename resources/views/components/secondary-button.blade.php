<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-base-200 border border-base-300 rounded-md font-medium text-sm text-content-light uppercase tracking-wide shadow-sm hover:bg-base-300 hover:border-primary/50 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:ring-offset-1 focus:ring-offset-base-100 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 ease-out']) }}>
    {{ $slot }}
</button>
