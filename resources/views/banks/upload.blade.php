<x-layouts.main :title="'Upload Bank Statement'" :contentHeader="'Upload Bank Statement PDF'">

    @if ($errors->any())
        <x-adminlte-alert theme="danger" title="Upload Error">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-adminlte-alert>
    @endif

    <form action="{{ route('bank.parse.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <x-adminlte-input-file name="statement" label="Bank Statement PDF" igroup-size="md" required>
            <x-slot name="prependSlot">
                <div class="input-group-text bg-primary">
                    <i class="fas fa-file-pdf"></i>
                </div>
            </x-slot>
        </x-adminlte-input-file>

        <x-adminlte-button type="submit" label="Upload & Parse" theme="primary" icon="fas fa-upload" class="mt-3"/>
    </form>

    @push('custom-js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.querySelector('input[type="file"][name="statement"]');
            input.addEventListener('change', function () {
                const label = this.nextElementSibling;
                if (this.files.length > 0) {
                    label.innerText = this.files[0].name;
                }
            });
        });
    </script>
    @endpush

</x-layouts.main>
