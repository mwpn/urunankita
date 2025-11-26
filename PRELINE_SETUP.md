# Setup Preline UI untuk UrunanKita

## 🎨 Preline UI - Tailwind CSS Component Library

Preline UI adalah component library berbasis Tailwind CSS yang cocok untuk design minimalistic, clean, dan professional.

## 📦 Installation Steps

### 1. Install Dependencies

```bash
npm install
```

Ini akan menginstall:
- `tailwindcss` - CSS framework
- `@tailwindcss/forms` - Plugin untuk form styling
- `preline` - Preline UI component library

### 2. Build CSS

Untuk development (watch mode):
```bash
npm run dev
```

Untuk production (minified):
```bash
npm run build
```

### 3. Include CSS di Views

CSS yang sudah di-compile ada di: `public/css/output.css`

Contoh penggunaan di view:
```php
<link rel="stylesheet" href="<?= base_url('css/output.css') ?>">
```

## 🎯 Components yang Tersedia

Preline UI menyediakan banyak components yang bisa digunakan:

- **Layout**: Container, Grid, Columns
- **Base Components**: Buttons, Cards, Badges, Alerts, Progress
- **Forms**: Input, Select, Checkbox, Radio, Switch
- **Navigation**: Navbar, Sidebar, Tabs, Breadcrumb
- **Overlays**: Modal, Dropdown, Tooltip
- **Tables**: Data tables dengan styling
- **Charts**: Chart.js integration
- Dan banyak lagi...

## 📚 Documentation

Dokumentasi lengkap: https://preline.co/docs/index.html

## 🔧 Customization

### Font: Outfit
Font Outfit sudah dikonfigurasi di `tailwind.config.js`:
```js
fontFamily: {
  'outfit': ['Outfit', 'sans-serif'],
}
```

### Primary Color: Green (#4CAF50)
Primary color sudah diset ke hijau sesuai branding:
```js
colors: {
  primary: {
    500: '#4CAF50',
    // ... shades
  },
}
```

## 💡 Usage Examples

### Button
```html
<button type="button" class="py-2 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700">
  Button
</button>
```

### Card
```html
<div class="bg-white border border-gray-200 rounded-lg shadow-sm">
  <div class="p-6">
    <h3 class="text-lg font-semibold text-gray-800">Card Title</h3>
    <p class="mt-2 text-gray-600">Card content</p>
  </div>
</div>
```

### Modal
```html
<!-- Modal toggle button -->
<button type="button" class="py-2 px-4..." data-hs-overlay="#hs-basic-modal">
  Open modal
</button>

<!-- Modal -->
<div id="hs-basic-modal" class="hs-overlay hidden size-full fixed top-0 start-0 z-[80] overflow-x-hidden overflow-y-auto">
  <div class="hs-overlay-open:mt-7 hs-overlay-open:opacity-100 hs-overlay-open:duration-500 mt-0 opacity-0 ease-out transition-all sm:max-w-lg sm:w-full m-3 sm:m-0">
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm">
      <!-- Modal content -->
    </div>
  </div>
</div>
```

## 🚀 Next Steps

1. Jalankan `npm install`
2. Jalankan `npm run build` untuk generate CSS
3. Update views untuk menggunakan Preline UI components
4. Konsisten menggunakan utility classes dari Tailwind CSS

## 📝 Notes

- Pastikan jalankan `npm run build` setiap kali ada perubahan di `input.css` atau `tailwind.config.js`
- Untuk development, gunakan `npm run dev` untuk auto-rebuild
- File CSS output: `public/css/output.css`
- File JS Preline: akan di-copy ke `public/node_modules/preline/dist/preline.js` atau bisa di-link langsung dari `node_modules`

