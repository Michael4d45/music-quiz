#!/bin/bash

# Music Quiz Icon Generator
# Generates PWA icons from favicon.svg using ultra-high-res workflow
# This eliminates white fringes and ensures perfect transparency

set -e  # Exit on any error

echo "🎵 Generating Music Quiz icons from SVG..."

# Configuration
SVG_FILE="public/favicon.svg"
TEMP_SOURCE="public/source-8192.png"

# Icon sizes for PWA
ICON_SIZES=(72 96 128 144 152 192 384 512)

echo "📐 Creating ultra-high-res source (8192x8192)..."

# Generate ultra-high-res source with perfect transparency
convert -density 4800 -background none "$SVG_FILE" -alpha set -antialias "$TEMP_SOURCE"

echo "🎨 Generating PWA icons..."

# Generate all PWA icon sizes
for size in "${ICON_SIZES[@]}"; do
    echo "  → ${size}x${size}"
    convert "$TEMP_SOURCE" -resize "${size}x${size}" "public/icon-${size}x${size}.png"
done

echo "📱 Generating additional icons..."

# Generate Apple touch icon
convert "$TEMP_SOURCE" -resize 180x180 "public/apple-touch-icon.png"

# Generate favicon.ico with multiple sizes
convert "$TEMP_SOURCE" -define icon:auto-resize=16,32,48 "public/favicon.ico"

echo "🧹 Cleaning up temporary files..."

# Clean up
rm "$TEMP_SOURCE"

echo ""
echo "✅ All icons generated successfully!"
echo ""
echo "📋 Generated files:"
echo "  • PWA icons: public/icon-{72x72,96x96,128x128,144x144,152x152,192x192,384x384,512x512}.png"
echo "  • Apple touch icon: public/apple-touch-icon.png"
echo "  • Favicon: public/favicon.ico"
echo ""
echo "🎵 Your musical note icons are ready with perfect transparency!"



