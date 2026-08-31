<?php

/**
 * A curated, department-grouped library of e-commerce category names a
 * seller can pick from when adding a product, instead of only being able
 * to type one from scratch. Shown in products/_form.blade.php grouped
 * under <optgroup> by department; picking one find-or-creates a real,
 * business-scoped Category (App\Models\Category) exactly the way the
 * existing "+ Add new category…" free-text option already does — see
 * ProductController::resolveCategoryId() and App\Rules\ValidCategorySelection.
 *
 * Deliberately just names, not a separate "master category" table this
 * needs to stay in sync with — each business's own Category rows (already
 * tenant-scoped, already how the rest of the app works) are still the
 * only real source of truth once a seller has picked something.
 */
return [
    'Electronics' => [
        'Phones & Tablets',
        'Computers & Laptops',
        'Computer Accessories',
        'Cameras & Photography',
        'TV, Audio & Home Theater',
        'Wearable Technology',
        'Gaming & Consoles',
        'Networking & Internet Devices',
    ],
    'Appliances' => [
        'Home Appliances',
        'Kitchen Appliances',
        'Air Conditioning & Heating',
        'Laundry & Cleaning Appliances',
    ],
    'Fashion' => [
        "Men's Clothing",
        "Women's Clothing",
        "Kids' Clothing",
        'Shoes & Footwear',
        'Bags & Luggage',
        'Jewelry & Watches',
        'Sunglasses & Eyewear',
        'Traditional & Ethnic Wear',
        'Underwear & Sleepwear',
        'Fashion Accessories',
    ],
    'Beauty & Personal Care' => [
        'Skincare',
        'Makeup & Cosmetics',
        'Haircare & Extensions',
        'Fragrances & Perfumes',
        'Personal Hygiene',
        "Men's Grooming",
        'Nail Care',
    ],
    'Health & Wellness' => [
        'Vitamins & Supplements',
        'Medical Supplies & Equipment',
        'Fitness Equipment',
        'Sexual Wellness',
        'Mobility & Daily Living Aids',
    ],
    'Home & Living' => [
        'Furniture',
        'Home Decor',
        'Bedding & Bath',
        'Kitchen & Dining',
        'Storage & Organization',
        'Lighting',
        'Rugs & Carpets',
        'Curtains & Window Treatments',
    ],
    'Groceries & Food' => [
        'Fresh Produce',
        'Packaged Foods',
        'Beverages',
        'Snacks & Confectionery',
        'Baking Supplies',
        'Spices & Condiments',
        'Frozen Foods',
        'Dairy & Eggs',
    ],
    'Baby & Kids' => [
        'Baby Clothing',
        'Diapers & Wipes',
        'Baby Gear (Strollers, Car Seats)',
        'Baby Feeding',
        'Toys & Games',
        'School Supplies',
    ],
    'Sports & Outdoors' => [
        'Sportswear',
        'Exercise & Fitness Gear',
        'Outdoor & Camping',
        'Bicycles & Accessories',
        'Team Sports Equipment',
        'Swimming & Water Sports',
    ],
    'Automotive' => [
        'Car Accessories',
        'Car Parts',
        'Motorcycle Parts & Accessories',
        'Car Care & Maintenance',
        'Tyres & Wheels',
    ],
    'Books, Office & Stationery' => [
        'Books',
        'Office Supplies',
        'Stationery',
        'Art & Craft Supplies',
        'Musical Instruments',
        'Printers & Ink',
    ],
    'Home Improvement & Garden' => [
        'Garden & Outdoor Living',
        'Tools & Home Improvement',
        'Building Materials',
        'Electrical & Plumbing Supplies',
        'Pet Supplies',
    ],
    'Other' => [
        'Event & Party Supplies',
        'Wedding Supplies',
        'Handmade & Crafts',
        'Digital Products & Software',
        'Gift Cards & Vouchers',
        'Industrial & Scientific Supplies',
        'Agriculture & Farming Supplies',
        'Second-Hand & Refurbished',
    ],
];
