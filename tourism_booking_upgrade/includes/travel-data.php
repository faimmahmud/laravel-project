<?php
// Luxury tourism data and image links used for static sections and seed defaults.

function travel_img(string $seed, int $w = 1600, int $h = 1000): string {
    return 'https://picsum.photos/seed/' . rawurlencode($seed) . '/' . $w . '/' . $h;
}

$site = [
    'brand' => 'Aurelia Travel',
    'tagline' => 'Luxury journeys, curated with cinematic detail.',
    'primary' => 'Royal blue',
    'accent' => 'Gold',
];

$heroSlides = [
    [
        'title' => 'Where premium travel feels effortless',
        'subtitle' => 'Arc-style sections, polished motion, and full-screen storytelling.',
        'image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1800&q=80',
        'cta' => 'Explore Tours',
        'link' => '#packages'
    ],
    [
        'title' => 'Mountain escapes with a luxury finish',
        'subtitle' => 'Clean typography, layered glass panels, and immersive visuals.',
        'image' => 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=1800&q=80',
        'cta' => 'View Destinations',
        'link' => '#destinations'
    ],
    [
        'title' => 'City nights, resort mornings, endless horizons',
        'subtitle' => 'A premium startup feel with elegant motion and modern UI hierarchy.',
        'image' => 'https://images.unsplash.com/photo-1482192596544-9eb780fc7f66?auto=format&fit=crop&w=1800&q=80',
        'cta' => 'Book Now',
        'link' => '#booking'
    ]
];

$stats = [
    ['value' => '120+', 'label' => 'Luxury itineraries'],
    ['value' => '45', 'label' => 'Countries featured'],
    ['value' => '98%', 'label' => 'Guest satisfaction'],
    ['value' => '24/7', 'label' => 'Concierge support'],
];

$featuredTours = [
    [
        'title' => 'Maldives Horizon Escape',
        'price' => '$2,890',
        'rating' => '4.9',
        'days' => '6 Days',
        'image' => travel_img('maldives-escape'),
        'description' => 'Private villas, turquoise water, and a quiet luxury experience designed for relaxation.',
        'features' => ['Ocean villa', 'Sunset cruise', 'Airport transfer'],
        'category' => 'beach'
    ],
    [
        'title' => 'Swiss Alpine Signature',
        'price' => '$3,740',
        'rating' => '5.0',
        'days' => '8 Days',
        'image' => travel_img('swiss-alps'),
        'description' => 'Panoramic mountain routes, lakeside stays, and curated scenic experiences.',
        'features' => ['Rail pass', 'Lake views', 'Private guide'],
        'category' => 'mountain'
    ],
    [
        'title' => 'Dubai Future Luxe',
        'price' => '$2,150',
        'rating' => '4.8',
        'days' => '5 Days',
        'image' => travel_img('dubai-luxe'),
        'description' => 'Skyline stays, desert highlights, and premium city experiences with sleek transport.',
        'features' => ['Sky lounge', 'Desert safari', 'VIP transfers'],
        'category' => 'city'
    ],
    [
        'title' => 'Bali Calm Retreat',
        'price' => '$1,980',
        'rating' => '4.9',
        'days' => '7 Days',
        'image' => travel_img('bali-retreat'),
        'description' => 'Tropical wellness, curated beaches, and modern design-led resorts.',
        'features' => ['Spa access', 'Jungle suite', 'Beach club'],
        'category' => 'beach'
    ],
    [
        'title' => 'Paris Iconic Week',
        'price' => '$2,430',
        'rating' => '4.7',
        'days' => '4 Days',
        'image' => travel_img('paris-iconic'),
        'description' => 'A polished European escape built around culture, cuisine, and elegant stays.',
        'features' => ['City pass', 'Fine dining', 'Private driver'],
        'category' => 'city'
    ],
    [
        'title' => 'Iceland Aurora Journey',
        'price' => '$3,110',
        'rating' => '5.0',
        'days' => '6 Days',
        'image' => travel_img('iceland-aurora'),
        'description' => 'Frozen landscapes, warm lodges, and dramatic scenery built for storytelling.',
        'features' => ['Northern lights', 'Hot springs', 'Lodges'],
        'category' => 'nature'
    ],
];

$destinations = [
    [
        'name' => 'Santorini',
        'country' => 'Greece',
        'image' => travel_img('santorini'),
        'tag' => 'Sunset islands',
        'summary' => 'Cliffside views, white architecture, and signature Aegean calm.',
        'color' => 'blue'
    ],
    [
        'name' => 'Kyoto',
        'country' => 'Japan',
        'image' => travel_img('kyoto'),
        'tag' => 'Tradition refined',
        'summary' => 'Historic lanes, serene gardens, and elegant seasonal experiences.',
        'color' => 'purple'
    ],
    [
        'name' => 'Queenstown',
        'country' => 'New Zealand',
        'image' => travel_img('queenstown'),
        'tag' => 'Adventure luxe',
        'summary' => 'Alpine air, lake views, and premium outdoor journeys.',
        'color' => 'gold'
    ],
    [
        'name' => 'Marrakesh',
        'country' => 'Morocco',
        'image' => travel_img('marrakesh'),
        'tag' => 'Color & culture',
        'summary' => 'Textured markets, boutique riads, and warm desert energy.',
        'color' => 'rose'
    ],
    [
        'name' => 'Cape Town',
        'country' => 'South Africa',
        'image' => travel_img('cape-town'),
        'tag' => 'Coast & mountain',
        'summary' => 'Ocean roads, Table Mountain, and a globally inspired food scene.',
        'color' => 'green'
    ],
    [
        'name' => 'Reykjavik',
        'country' => 'Iceland',
        'image' => travel_img('reykjavik'),
        'tag' => 'Nordic glow',
        'summary' => 'Minimalist design, volcanic landscapes, and premium calm.',
        'color' => 'indigo'
    ],
    [
        'name' => 'Bora Bora',
        'country' => 'French Polynesia',
        'image' => travel_img('bora-bora'),
        'tag' => 'Private lagoon',
        'summary' => 'Water villas and postcard-perfect blues with high-end privacy.',
        'color' => 'cyan'
    ],
    [
        'name' => 'Singapore',
        'country' => 'Singapore',
        'image' => travel_img('singapore'),
        'tag' => 'Urban future',
        'summary' => 'Architectural gardens, clean design, and luxury retail energy.',
        'color' => 'amber'
    ],
];

$testimonials = [
    ['name' => 'Ayesha Rahman', 'role' => 'Luxury traveler', 'quote' => 'The site feels like a premium brand launch. The full-screen visuals are exactly what we wanted.', 'image' => travel_img('person-1', 400, 400)],
    ['name' => 'Daniel Roy', 'role' => 'Travel curator', 'quote' => 'Elegant, smooth, and easy to use. The booking flow is fast and the visuals are top-tier.', 'image' => travel_img('person-2', 400, 400)],
    ['name' => 'Mina Alvarez', 'role' => 'Agency founder', 'quote' => 'The new layout feels cinematic. It looks far more premium than a normal template.', 'image' => travel_img('person-3', 400, 400)],
];

$packages = [
    [
        'id' => 'pkg-mald',
        'title' => 'Maldives Horizon Escape',
        'country' => 'Maldives',
        'price' => '$2,890',
        'rating' => '4.9',
        'days' => '6 Days',
        'image' => travel_img('pkg-maldives'),
        'description' => 'Private villas, quiet beaches, and a luxury experience shaped around calm.',
        'details' => ['Private villa', 'Airport lounge', 'Sea excursions', 'Spa access'],
        'category' => 'beach'
    ],
    [
        'id' => 'pkg-swiss',
        'title' => 'Swiss Alpine Signature',
        'country' => 'Switzerland',
        'price' => '$3,740',
        'rating' => '5.0',
        'days' => '8 Days',
        'image' => travel_img('pkg-switzerland'),
        'description' => 'A refined mountain journey with rail routes, lakeside stays, and scenic stops.',
        'details' => ['Rail pass', 'Lake-view suites', 'Private guide', 'Fondue evenings'],
        'category' => 'mountain'
    ],
    [
        'id' => 'pkg-dubai',
        'title' => 'Dubai Future Luxe',
        'country' => 'UAE',
        'price' => '$2,150',
        'rating' => '4.8',
        'days' => '5 Days',
        'image' => travel_img('pkg-dubai'),
        'description' => 'High-rise views, desert highlights, and an ultra-modern city mood.',
        'details' => ['Sky lounge', 'Desert safari', 'VIP transfers', 'City tour'],
        'category' => 'city'
    ],
    [
        'id' => 'pkg-bali',
        'title' => 'Bali Calm Retreat',
        'country' => 'Indonesia',
        'price' => '$1,980',
        'rating' => '4.9',
        'days' => '7 Days',
        'image' => travel_img('pkg-bali'),
        'description' => 'Beach serenity, wellness experiences, and a design-led resort stay.',
        'details' => ['Spa ritual', 'Jungle suite', 'Private pool', 'Beach club'],
        'category' => 'beach'
    ],
];

$countries = [
    ['name' => 'France', 'region' => 'Europe', 'image' => travel_img('country-france'), 'reason' => 'Paris, Provence, Riviera moods.', 'flag' => 'FR'],
    ['name' => 'Japan', 'region' => 'Asia', 'image' => travel_img('country-japan'), 'reason' => 'Kyoto, Tokyo, and seasonal design beauty.', 'flag' => 'JP'],
    ['name' => 'Italy', 'region' => 'Europe', 'image' => travel_img('country-italy'), 'reason' => 'Coastlines, cuisine, and iconic cities.', 'flag' => 'IT'],
    ['name' => 'United Arab Emirates', 'region' => 'Middle East', 'image' => travel_img('country-uae'), 'reason' => 'Luxury cityscape and desert contrast.', 'flag' => 'AE'],
    ['name' => 'Switzerland', 'region' => 'Europe', 'image' => travel_img('country-switzerland'), 'reason' => 'Alps, lakes, and premium rail journeys.', 'flag' => 'CH'],
    ['name' => 'New Zealand', 'region' => 'Oceania', 'image' => travel_img('country-new-zealand'), 'reason' => 'Epic nature and cinematic road trips.', 'flag' => 'NZ'],
    ['name' => 'Australia', 'region' => 'Oceania', 'image' => travel_img('country-australia'), 'reason' => 'Beaches, cities, and iconic landscapes.', 'flag' => 'AU'],
    ['name' => 'Spain', 'region' => 'Europe', 'image' => travel_img('country-spain'), 'reason' => 'Art, food, and vibrant coastal towns.', 'flag' => 'ES'],
    ['name' => 'Greece', 'region' => 'Europe', 'image' => travel_img('country-greece'), 'reason' => 'Islands, sunsets, and relaxed luxury.', 'flag' => 'GR'],
    ['name' => 'Turkey', 'region' => 'Europe / Asia', 'image' => travel_img('country-turkey'), 'reason' => 'History, coast, and dramatic scenery.', 'flag' => 'TR'],
    ['name' => 'Iceland', 'region' => 'Europe', 'image' => travel_img('country-iceland'), 'reason' => 'Auroras, glaciers, and natural drama.', 'flag' => 'IS'],
    ['name' => 'Canada', 'region' => 'North America', 'image' => travel_img('country-canada'), 'reason' => 'Lakes, mountains, and cosmopolitan calm.', 'flag' => 'CA'],
    ['name' => 'USA', 'region' => 'North America', 'image' => travel_img('country-usa'), 'reason' => 'Big city energy and national parks.', 'flag' => 'US'],
    ['name' => 'Brazil', 'region' => 'South America', 'image' => travel_img('country-brazil'), 'reason' => 'Nature, coastline, and vibrant rhythm.', 'flag' => 'BR'],
    ['name' => 'Peru', 'region' => 'South America', 'image' => travel_img('country-peru'), 'reason' => 'Andes, culture, and ancient wonders.', 'flag' => 'PE'],
    ['name' => 'Morocco', 'region' => 'Africa', 'image' => travel_img('country-morocco'), 'reason' => 'Desert, riads, and textured markets.', 'flag' => 'MA'],
    ['name' => 'South Africa', 'region' => 'Africa', 'image' => travel_img('country-south-africa'), 'reason' => 'Safaris, coastlines, and Table Mountain.', 'flag' => 'ZA'],
    ['name' => 'Egypt', 'region' => 'Africa', 'image' => travel_img('country-egypt'), 'reason' => 'Nile, pyramids, and timeless history.', 'flag' => 'EG'],
    ['name' => 'Thailand', 'region' => 'Asia', 'image' => travel_img('country-thailand'), 'reason' => 'Islands, street life, and premium resorts.', 'flag' => 'TH'],
    ['name' => 'Singapore', 'region' => 'Asia', 'image' => travel_img('country-singapore'), 'reason' => 'Future-forward city design and luxury.', 'flag' => 'SG'],
    ['name' => 'Maldives', 'region' => 'Asia', 'image' => travel_img('country-maldives'), 'reason' => 'Overwater villas and blue-water privacy.', 'flag' => 'MV'],
    ['name' => 'India', 'region' => 'Asia', 'image' => travel_img('country-india'), 'reason' => 'Culture, color, food, and contrasts.', 'flag' => 'IN'],
    ['name' => 'South Korea', 'region' => 'Asia', 'image' => travel_img('country-south-korea'), 'reason' => 'Style, technology, and city energy.', 'flag' => 'KR'],
    ['name' => 'Portugal', 'region' => 'Europe', 'image' => travel_img('country-portugal'), 'reason' => 'Coast, design, and warm urban charm.', 'flag' => 'PT'],
    ['name' => 'Netherlands', 'region' => 'Europe', 'image' => travel_img('country-netherlands'), 'reason' => 'Canals, cycling, and elegant cities.', 'flag' => 'NL'],
    ['name' => 'Norway', 'region' => 'Europe', 'image' => travel_img('country-norway'), 'reason' => 'Fjords, roads, and Arctic light.', 'flag' => 'NO'],
    ['name' => 'Finland', 'region' => 'Europe', 'image' => travel_img('country-finland'), 'reason' => 'Lakes, calm, and design culture.', 'flag' => 'FI'],
    ['name' => 'Austria', 'region' => 'Europe', 'image' => travel_img('country-austria'), 'reason' => 'Classical beauty and alpine escapes.', 'flag' => 'AT'],
    ['name' => 'Czech Republic', 'region' => 'Europe', 'image' => travel_img('country-czech'), 'reason' => 'Old-world streets and refined atmosphere.', 'flag' => 'CZ'],
    ['name' => 'Vietnam', 'region' => 'Asia', 'image' => travel_img('country-vietnam'), 'reason' => 'Coasts, food, and layered landscapes.', 'flag' => 'VN'],
    ['name' => 'Philippines', 'region' => 'Asia', 'image' => travel_img('country-philippines'), 'reason' => 'Island hopping and tropical ease.', 'flag' => 'PH'],
    ['name' => 'Sri Lanka', 'region' => 'Asia', 'image' => travel_img('country-sri-lanka'), 'reason' => 'Tea country, beaches, and culture.', 'flag' => 'LK'],
    ['name' => 'Indonesia', 'region' => 'Asia', 'image' => travel_img('country-indonesia'), 'reason' => 'Island diversity and resort living.', 'flag' => 'ID'],
    ['name' => 'Chile', 'region' => 'South America', 'image' => travel_img('country-chile'), 'reason' => 'Long landscapes and dramatic horizons.', 'flag' => 'CL'],
    ['name' => 'Argentina', 'region' => 'South America', 'image' => travel_img('country-argentina'), 'reason' => 'City style, wine regions, and Patagonia.', 'flag' => 'AR'],
    ['name' => 'Kenya', 'region' => 'Africa', 'image' => travel_img('country-kenya'), 'reason' => 'Safari routes and iconic wildlife.', 'flag' => 'KE'],
    ['name' => 'Tanzania', 'region' => 'Africa', 'image' => travel_img('country-tanzania'), 'reason' => 'Safari, coast, and mountain escapes.', 'flag' => 'TZ'],
    ['name' => 'Saudi Arabia', 'region' => 'Middle East', 'image' => travel_img('country-saudi'), 'reason' => 'Modern luxury and desert heritage.', 'flag' => 'SA'],
    ['name' => 'Qatar', 'region' => 'Middle East', 'image' => travel_img('country-qatar'), 'reason' => 'Premium architecture and waterfront city life.', 'flag' => 'QA'],
    ['name' => 'Oman', 'region' => 'Middle East', 'image' => travel_img('country-oman'), 'reason' => 'Mountains, coast, and elegant calm.', 'flag' => 'OM'],
];
