<div class="sidebar green">
    <div class="sidebar-wrapper">
        <div class="logo">
            <a href="#" class="simple-text logo-mini">{{ __('TD') }}</a>
            <a href="#" class="simple-text logo-normal">{{ __('CryptoTrade') }}</a>
        </div>
        <ul class="nav">
            <li @if ($pageSlug == 'dashboard') class="active " @endif>
                <a href="{{ route('home') }}">
                    <i class="tim-icons icon-chart-pie-36"></i>
                    <p>{{ __('Dashboard') }}</p>
                </a>
            </li>
            <li @if ($pageSlug == 'profile') class="active " @endif>
                <a href="{{ route('profile.edit')  }}">
                    <i class="tim-icons icon-single-02"></i>
                    <p>{{ __('User Profile') }}</p>
                </a>
            </li>
            <li @if ($pageSlug == 'coins') class="active " @endif>
                <a href="{{ route('pages.coins') }}">
                    <i class="tim-icons icon-atom"></i>
                    <p>{{ __('Coins') }}</p>
                </a>
            </li>
            <li @if ($pageSlug == 'orderbook') class="active " @endif>
                <a href="{{ route('coins.orderbook') }}">
                    <i class="tim-icons icon-cart"></i>
                    <p>{{ __('Order Book') }}</p>
                </a>
            </li>
            <li @if ($pageSlug == 'buysellpage') class="active " @endif>
                <a href="{{ route('pages.SalePage') }}">
                    <i class="tim-icons icon-cart"></i>
                    <p>{{ __('Buy or Sell') }}</p>
                </a>
            </li>
            <li @if ($pageSlug == 'settings') class="active " @endif>
                <a href="{{ route('pages.settings') }}">
                    <i class="tim-icons icon-puzzle-10"></i>
                    <p>{{ __('Setting') }}</p>
                </a>
            </li>


        </ul>
    </div>
</div>
