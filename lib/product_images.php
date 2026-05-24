<?php
// This script handles uploading, resizing, and saving product images safely to the server.

/**
 * Product image helper
 * Returns uploaded product image if available, otherwise returns a copyright-free placeholder (picsum.photos) seeded by id
 */
function default_product_image(int $productId = 0, ?string $uploadedFilename = null, int $size = 400): string
{
    // If an uploaded filename is provided, use it
    if ($uploadedFilename !== null && trim($uploadedFilename) !== '') {
        $uploadPath = '/assets/images/products/' . rawurlencode($uploadedFilename);
        return $uploadPath;
    }

    // Fall back to copyright-free placeholder seeded by product id
    $seed = $productId > 0 ? (string) $productId : 'placeholder';
    return sprintf('https://picsum.photos/seed/%s/%d/%d', rawurlencode($seed), $size, $size);
}
