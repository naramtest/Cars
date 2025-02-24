<span x-data>
    <template x-if="$wire.data.{{ $field }}">
        <span
            :style="{
color: $wire.data.{{ $field }}.length <= {{ $max }} ? '#02bb02':'red'}"
            class="font-medium"
            x-text="$wire.data.{{ $field }}.length + '/' + {{ $max }}"
        ></span>
    </template>
</span>
