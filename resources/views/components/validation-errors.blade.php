@props([
    'message' => 'Certaines informations sont incorrectes.',
    'dismissible' => true,
])

@if ($errors->any())
    <div class="alert alert-danger {{ $dismissible ? 'alert-dismissible fade show' : '' }}"
        role="alert">

        <div class="d-flex">

            <i class="bi bi-exclamation-triangle me-2 mt-1"></i>

            <div>

                <strong>
                    {{ $message }}
                </strong>

                <ul class="mb-0 mt-2">

                    @foreach ($errors->all() as $error)
                        <li>
                            {{ $error }}
                        </li>
                    @endforeach

                </ul>

            </div>

        </div>

        @if ($dismissible)
            <button type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Fermer">
            </button>
        @endif

    </div>
@endif
