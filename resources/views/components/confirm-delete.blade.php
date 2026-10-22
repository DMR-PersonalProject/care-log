@props(['entry'])

{{-- Delete button that asks first, using the browser's built-in dialog (Esc or Cancel closes it). --}}
<div x-data class="contents">
    <button type="button" class="btn btn-secondary text-red-700" @click="$refs.dialog.showModal()">Delete</button>

    <dialog x-ref="dialog" class="m-auto w-[min(28rem,calc(100%-2rem))] rounded-xl p-6 shadow-xl backdrop:bg-black/50">
        <h2 class="text-2xl font-bold">Delete this entry?</h2>
        <p class="mt-2 text-stone-700">
            {{ $entry->activity->label() }} at {{ $entry->displayTime() }} on {{ $entry->displayDate() }}.
            This cannot be undone.
        </p>

        <form method="POST" action="{{ route('entries.destroy', $entry) }}" class="mt-6 flex flex-wrap justify-end gap-3">
            @csrf
            @method('DELETE')
            <button type="button" class="btn btn-secondary" @click="$refs.dialog.close()" autofocus>Cancel</button>
            <button type="submit" class="btn btn-danger">Delete</button>
        </form>
    </dialog>
</div>
