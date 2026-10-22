@props(['name'])

@error($name)
    <p class="mt-1 text-base font-medium text-red-700">{{ $message }}</p>
@enderror
