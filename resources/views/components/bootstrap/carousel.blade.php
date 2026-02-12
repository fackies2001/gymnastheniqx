<div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
    {{-- Indicators --}}
    <ol class="carousel-indicators">
        {{-- {{ dd($images) }} --}}
        @foreach ($images as $index => $img)
            <li data-target="#carouselExampleIndicators" data-slide-to="{{ $index }}"
                class="{{ $index === 0 ? 'active' : '' }}"></li>
        @endforeach
    </ol>

    {{-- Carousel items --}}
    <div class="carousel-inner">
        @foreach ($images as $index => $img)
            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                <img class="d-block w-100" src="{{ $img }}" alt="Slide {{ $index + 1 }}">
            </div>
        @endforeach
    </div>

    {{-- Controls --}}
    <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
    </a>
    <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </a>
</div>
