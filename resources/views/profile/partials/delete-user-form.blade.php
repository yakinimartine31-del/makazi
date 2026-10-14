<section class="mb-4">
    <header class="mb-4">
        <h2 class="h5 font-weight-bold text-danger">{{ __('Delete Account') }}</h2>
        <p class="text-muted mb-0">{{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}</p>
    </header>

    <div>
        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#confirm-user-deletion-modal">
            {{ __('Delete Account') }}
        </button>
    </div>

    <div class="modal fade" id="confirm-user-deletion-modal" tabindex="-1" aria-labelledby="confirm-user-deletion-label" aria-hidden="true">
        <form method="post" action="{{ route('profile.destroy') }}">
            @csrf
            @method('delete')

            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirm-user-deletion-label">{{ __('Are you sure you want to delete your account?') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted mb-0">{{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}</p>
                    </div>
                    <div class="modal-footer">
                        <x-secondary-button data-dismiss="modal">
                            {{ __('Cancel') }}
                        </x-secondary-button>

                        <div class="col-md-4">
                            <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />
                            <x-text-input
                                id="password"
                                name="password"
                                type="password"
                                class="form-control"
                                placeholder="{{ __('Password') }}"
                            />
                            <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
                        </div>

                        <x-danger-button class="ml-3">
                            {{ __('Delete Account') }}
                        </x-danger-button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>
