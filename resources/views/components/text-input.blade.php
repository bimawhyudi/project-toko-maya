@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-garis bg-surface focus:border-primary focus:ring-primary/20 rounded-lg shadow-sm transition']) }}>