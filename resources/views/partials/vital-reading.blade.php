{{-- One reading in the vitals table; out-of-range readings are bold red with "(High)" or "(Low)". --}}
@php($flag = $entry->vitalFlag($field))
@if ($entry->{$field} === null)
    {{ $empty ?? '' }}
@else
    <span @class(['font-bold text-red-800' => $flag])>{{ $entry->{$field} }}{{ $suffix ?? '' }}@if ($flag) ({{ $flag }})@endif</span>
@endif
