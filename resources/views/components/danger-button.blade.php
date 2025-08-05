<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-error border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-error/80 active:bg-error/90 focus:outline-none focus:ring-2 focus:ring-error focus:ring-offset-2 focus:ring-offset-base-100 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
