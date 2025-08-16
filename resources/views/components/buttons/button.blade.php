@props([
    'mp_preference_id'
])
@if($mp_preference_id)
    @pushonce('scripts')
        <script src="https://sdk.mercadopago.com/js/v2"></script>

        <script>
            const mp_default = new MercadoPago('{{config('mercadopago.key')}}');
            mp_default.bricks().create("wallet", "default_preference", {
                initialization: {
                    preferenceId: "{{ $mp_preference_id }}",
                    redirectMode: "modal"
                },
            });
        </script>
    @endpushonce
    <div id="default_preference"></div>
@endif
