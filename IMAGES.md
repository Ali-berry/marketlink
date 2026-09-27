# Images

## Where the photos come from (AI acknowledgement)

All photos in `public/images/**/*.webp` were generated with **Google Gemini**
(image generation), not taken by us and not downloaded from a stock site. The
one exception is `markets/market-park-tents.webp`, which was generated with
**ChatGPT** (OpenAI image generation). The original files are kept untouched in
`UI assets/generated-images/` (the second batch is in `new/`). The copies in
`public/images` are cropped, resized and converted to WebP (quality 80, every
file under 300 KB) for the SRS performance requirement. None of them were
upscaled, so the size of each copy is at most the size of its original.

The older `.svg` files next to them are the flat placeholders from before. They
are not used by any page right now but are kept on purpose as spare fallbacks.

## public/images/site/
| File | Used on | Original Gemini file |
|---|---|---|
| hero-produce-stall.webp | Home hero slide 1, customer dashboard banner | Gemini_Generated_Image_dngu3a... |
| hero-farmer-at-stall.webp | Home hero slide 2 | Gemini_Generated_Image_4di0uf... |
| hero-market-street.webp | Home hero slide 3 (find a market) | Gemini_Generated_Image_ckl7fz... |
| hero-farmer-order-list.webp | Home hero slide 4 (sell at a market) | Gemini_Generated_Image_57simj... |
| about-farm-field.webp | About hero | Gemini_Generated_Image_owpgs3... (cropped above a visible seam) |
| about-packing-crate.webp | About, "Why we built this" | Gemini_Generated_Image_qfvb7p... |
| about-local-first.webp | About, "Local first" card | Gemini_Generated_Image_l2i8ec... (right side only, the rest has text baked in) |
| about-farmers-we-vet.webp | About, "Farmers we vet" card (no farmer uses this photo any more) | Gemini_Generated_Image_piz579... |
| about-no-wasted-trips.webp | About, "No wasted trips" card | Gemini_Generated_Image_9vusqw... |
| community-hero.webp | Community page hero | Gemini_Generated_Image_j3vqx2... (cropped above a dark band) |
| contact-hero.webp | Contact hero | Gemini_Generated_Image_j8tk5k... |
| auth-evening-market.webp | Login, register and other auth pages | Gemini_Generated_Image_ikhyw5... (dark box in the middle lightened, see below) |

## public/images/products/
| File | Used for | Original file |
|---|---|---|
| milk.webp, eggs.webp, yogurt.webp, butter.webp | Desi Dairy Corner products | first batch |
| sourdough.webp, wheat-bread.webp, banana-cake.webp | Crust and Crumb products | first batch |
| vine-tomatoes.webp | Vine tomatoes | new/pjjkw9... |
| heirloom-tomatoes.webp | Heirloom tomatoes | new/inzpyc... |
| spinach.webp | Spinach | new/uvfv7w... |
| carrots.webp | Carrots | new/lrvjds... |
| cucumbers.webp | Cucumbers | new/vfs3xy... (new/ytsvny... is a spare with slices) |
| mint.webp | Fresh mint | new/j4ike8... |
| mangoes.webp | Valley mangoes | new/npiscn... |
| bananas.webp | Bananas | new/xbkeze... (the "(1)" copy is the same picture) |
| pomegranates.webp | Pomegranates | new/wlb36t..., whole photo |
| desert-pomegranates.webp | Desert pomegranates | new/wlb36t..., close crop on the open halves (640 px) |
| green-chile-salsa.webp | Roasted green chile salsa | new/e13v8b... (new/g1nbli... is the same picture) |
| mesquite-honey.webp | Raw mesquite honey | new/hbu7q1... |
| prickly-pear-jam.webp | Prickly pear jam | new/jcsv7i... |
| basil.webp | Fallback for the herbs category | 785pl1... |
| category-vegetables.webp | Fallback for vegetables (896 px) | qfvb7p..., the produce box only |
| category-fruits.webp | Fallback for fruits (900 px) | nnh3lr..., fruit crates and basket |
| category-general.webp | Fallback for honey and preserves, and any other category | first batch |

Products without their own photo have `image_path = null`, and
`Product::imageUrl()` picks the category photo. Right now that is only Guava.
When a real photo is added, just put the file here and set `image_path` in
`DemoAccountSeeder`.

## public/images/farmers/
All farmer photos are square portraits. Pages that show them in a wide frame use
`object-[center_20%]` (the taller farmer profile banner uses 15%) so the face stays in
the crop.

| File | Farmer | Original file |
|---|---|---|
| green-valley.webp | Green Valley Farm, man with kale and greens | farmers-v2/fkk0gb... |
| sunrise-orchard.webp | Sunrise Orchard, woman with a basket of mangoes and guava | farmers-v2/2a5qps... |
| desi-dairy.webp | Desi Dairy Corner, older man with milk bottles | farmers-v2/7e2n1p... |
| crust-crumb.webp | Crust and Crumb, woman with sourdough loaves (cropped left of a "Fresh Sourdough Daily" chalkboard) | farmers-v2/fbgm39... |
| mesilla-valley.webp | Mesilla Valley Growers, man in a straw hat with chiles and tomatoes | farmers-v2/auni1s... |
| wildflower-honey.webp | Wildflower Honey Co., beekeeper with hives (768 px) | new/tr37ah... |
| farmer-generic.webp | Any farmer without a cover photo | first batch |

The five `farmers-v2` photos are 1408 x 768 originals, cut to a 768 px square
around the farmer. Each one is a different person. They replaced the first-batch
farmer photos under the same file names, so the seeders and database did not
change. The About page's "Farmers we vet" photo (`site/about-farmers-we-vet.webp`)
is no longer the same picture as Mesilla Valley's.

## public/images/markets/
Every market has its own 4:3 cover (1000 x 750) for cards, and a `-wide` copy
cut from the full width of the original for the banner on the market detail
page. `Market::bannerImageUrl()` uses the `-wide` copy when it exists.

| File | Market | Original file |
|---|---|---|
| market-street-bunting.webp | Cowtown (Fort Worth) | ckl7fz... |
| market-covered-shed.webp | Dallas | new/jwr0ga... |
| market-desert-stall.webp | El Paso | new/yq60gy... |
| market-tent-row.webp | Houston | j8tk5k... |
| market-burlap-stall.webp | Pearl (San Antonio) | mafwfv... |
| market-park-tents.webp | Sustainable Food Center (Austin) | new/ChatGPT Image Sep 27, 2026... |

## Known problems in the Gemini originals
- `ikhyw5` (evening market): a dark see-through box in the middle. It was
  brightened back, but the blur inside it still shows a little. It sits behind
  the login card and a dark overlay, so it is hard to see. Worth regenerating.
- `l2i8ec` (community chat): the words "Connect with your community" and a
  "Join the conversation" button are baked into the picture. Only the clean
  right side is used.
- `j3vqx2` (farmer and customer laughing): dark band across the lower half.
  Only the part above it is used.
- `owpgs3` (farm field at dawn): the bottom half is a different field pasted on,
  with a hard line between them. Only the top half is used.
- None of the files have a visible Gemini sparkle watermark in the corners.
- The biggest originals are 1456 px wide, so hero images are 1200-1456 px and
  not 2000 px. They were not upscaled.
- `new/yq60gy` (desert market stall, El Paso): a chalkboard in the middle reads
  "Local Desert Provisions - Honey, Nuts & Chiles" and the jars have small
  "Desert Bloom Honey" labels. The spelling is right and it fits the scene, so it
  is used as is.
- `new/wlb36t` (pomegranates): there is only one pomegranate photo, so
  Pomegranates and Desert pomegranates use two different crops of it.
- None of the new files have a watermark or a stripe.

## Still missing
Products: guava (no photo was generated, it uses the fruits photo for now).
