<x-app-layout>
    <x-slot name="title">
        Tenant Dashboard
    </x-slot>

    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">Welcome, {{ auth()->user()->name }}</h1>

            <div class="card">
                <div class="card-header">
                    Your Account
                </div>
                <div class="card-body">
                    <p><strong>Name:</strong> {{ auth()->user()->name }}</p>
                @if (str_ends_with((string) auth()->user()->email, '@accounts.aura.invalid'))
                    <p><strong>WhatsApp:</strong> {{ auth()->user()->whatsapp_number }}</p>
                @else
                    <p><strong>Email:</strong> {{ auth()->user()->email }}</p>
                @endif
                    <p><strong>Role:</strong>
                        @if(auth()->user()->isAdmin())
                            <span class="badge bg-danger">Admin</span>
                        @elseif(auth()->user()->isOwner())
                            <span class="badge bg-success">Property Owner</span>
                        @elseif(auth()->user()->isTenant())
                            <span class="badge bg-info text-dark">Tenant</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
