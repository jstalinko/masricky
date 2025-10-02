// Import library yang dibutuhkan
const { Telegraf, Markup } = require('telegraf');
const axios = require('axios');
// get setting from ../storage/app/settings.json if exists
const fs = require('fs');
const path = require('path');
const settingsPath = path.join(__dirname, '..', 'storage', 'app', 'settings.json');
let settings = {};
if (fs.existsSync(settingsPath)) {
    settings = JSON.parse(fs.readFileSync(settingsPath));
}   

// --- KONFIGURASI ---
const BOT_TOKEN = settings.telegram_bot_token;
const API_BASE_URL = 'https://masricky.com/api';
// --- PERUBAHAN: URL Placeholder untuk gambar QRIS ---
const QRIS_IMAGE_URL = './qris.png'; // Ganti dengan URL gambar QRIS statis Anda jika punya

// Inisialisasi bot
const bot = new Telegraf(BOT_TOKEN);

// --- FUNGSI & DATA ---

// Objek sederhana untuk menyimpan data produk sementara
// Dalam aplikasi nyata, lebih baik menggunakan database atau cache seperti Redis
const productCache = new Map();

/**
 * Fungsi untuk mengambil dan menampilkan daftar kategori produk.
 * @param {object} ctx - Konteks dari Telegraf.
 * @param {boolean} isEdit - Menentukan apakah pesan harus diedit atau dikirim sebagai pesan baru.
 */
const showCategories = async (ctx, isEdit = false) => {
    try {
        const response = await axios.get(`${API_BASE_URL}/categories`);
        if (response.data && response.data.success) {
            const categories = response.data.data;
            const buttons = categories.map(category =>
                Markup.button.callback(category.name, `category_${category.id}`)
            );
            const keyboard = Markup.inlineKeyboard(buttons, { columns: 2 });
            const message = "🛍️ Silakan pilih kategori produk:";

            if (isEdit) {
                // Hapus gambar jika ada (misal dari invoice) dan edit teks
                await ctx.deleteMessage().catch(() => {}); // Abaikan error jika pesan sudah terhapus
                await ctx.reply(message, keyboard);
            } else {
                await ctx.reply(message, keyboard);
            }
        } else {
            await ctx.reply('❌ Maaf, gagal mengambil data kategori.');
        }
    } catch (error) {
        console.error("Error fetching categories:", error);
        await ctx.reply('Terjadi kesalahan saat menghubungi server.');
    }
};


// --- HANDLER BOT ---

// Menu Utama (Reply Keyboard)
const mainMenu = Markup.keyboard([
    ['🛒 Daftar Produk', '📜 Riwayat Order'],
    ['💰 Saldo Saya', '📢 Info Terbaru']
]).resize();

// Handler untuk perintah /start
// ... (kode bot Anda yang lain ada di sini)

// Handler untuk perintah /start dengan pengecekan dan registrasi otomatis
bot.command('start', async (ctx) => {
    // Memberi tahu pengguna bahwa bot sedang berpikir, agar tidak terasa hang
    await ctx.replyWithChatAction('typing');

    const telegramId = ctx.from.id;
    const userInfo = {
        telegram_id: telegramId,
        username: ctx.from.username || `user_${telegramId}`, // Fallback jika user tidak punya username
        first_name: ctx.from.first_name,
        last_name: ctx.from.last_name || null // Kirim null jika tidak ada
    };
        ctx.reply(settings.welcome_message || 'Selamat datang di Bot Kami! Silakan pilih opsi di bawah ini.', mainMenu);
    try {

        try {
            await axios.get(`${API_BASE_URL}/user/${telegramId}`);
            
            ctx.reply(
                `Selamat datang kembali! ${userInfo.username} 👋 `,
                mainMenu
            );
        } catch (error) {
            // --- Langkah 2: Jika user tidak ada (error 404), daftarkan ---
            if (error.response && error.response.status === 404) {
                
                await axios.post(`${API_BASE_URL}/register`, userInfo);
             
                ctx.reply(
                    `Selamat datang ${userInfo.username} ! Akun Anda telah berhasil dibuat. Silakan pilih opsi di bawah ini.`,
                    mainMenu
                );
            } else {
                // Tangani error lain (misal server API mati)
                throw error; // Lemparkan error ke blok catch utama
            }
        }
    } catch (error) {
        // --- Langkah 3: Tangani jika ada kesalahan koneksi atau server ---
        console.error("Error saat proses start/register:", error.message);
        ctx.reply('Maaf, terjadi masalah saat menghubungkan ke server. Silakan coba lagi nanti.');
    }
});


// --- Handler untuk Tombol Menu ---
bot.hears('🛒 Daftar Produk', (ctx) => showCategories(ctx, false));

bot.hears('💰 Saldo Saya', (ctx) => ctx.reply('🚧 Fitur pengecekan saldo sedang dalam pengembangan!'));
bot.hears('📢 Info Terbaru', (ctx) => ctx.reply('Tidak ada informasi terbaru saat ini.'));

bot.hears('📜 Riwayat Order', async (ctx) => {
    const telegramId = ctx.from.id;
    
    // Kirim pesan loading awal
    const loadingMessage = await ctx.reply('⏳ Mengambil riwayat pesanan Anda...');

    try {
        // Panggil API untuk mendapatkan riwayat
        const response = await axios.get(`${API_BASE_URL}/order/history/${telegramId}`);

        if (response.data && response.data.success) {
            const orders = response.data.data;

            // Cek jika tidak ada riwayat pesanan
            if (orders.length === 0) {
                await ctx.telegram.editMessageText(
                    ctx.chat.id,
                    loadingMessage.message_id,
                    null,
                    'Anda belum memiliki riwayat pesanan.'
                );
                return;
            }

            // Siapkan header pesan
            let historyMessage = '📜 **Riwayat Pesanan Anda**\n\n';
            
            // Fungsi kecil untuk memberi emoji pada status
            const getStatusEmoji = (status) => {
                switch (status.toUpperCase()) {
                    case 'PENDING': return '⏳';
                    case 'SUCCESS':
                    case 'PAID':
                    case 'COMPLETED': return '✅';
                    case 'FAILED': return '❌';
                    case 'EXPIRED': return '⌛️';
                    default: return '➡️';
                }
            };

            // Format setiap pesanan
            orders.slice(0, 10).forEach(order => { // Batasi hanya 10 pesanan terbaru agar tidak terlalu panjang
                const date = new Date(order.created_at);
                const formattedDate = date.toLocaleDateString('id-ID', {
                    day: '2-digit', month: 'short', year: 'numeric'
                });

                historyMessage += `**Invoice:** \`INV-${order.id}\`\n` +
                                `**Produk:** ${order.product.name}\n` +
                                `**Status:** ${getStatusEmoji(order.status)} ${order.status}\n` +
                                `**Tanggal:** ${formattedDate}\n` +
                                `--------------------\n`;
            });

            // Edit pesan loading dengan data riwayat yang sudah diformat
            await ctx.telegram.editMessageText(
                ctx.chat.id,
                loadingMessage.message_id,
                null,
                historyMessage,
                { parse_mode: 'Markdown' }
            );

        } else {
            await ctx.telegram.editMessageText(
                ctx.chat.id,
                loadingMessage.message_id,
                null,
                'Gagal mengambil data riwayat pesanan.'
            );
        }
    } catch (error) {
        console.error("API Error at /order/history:", error.message);
        await ctx.telegram.editMessageText(
            ctx.chat.id,
            loadingMessage.message_id,
            null,
            'Maaf, terjadi kesalahan pada server. Coba lagi nanti.'
        );
    }
});

// Handler untuk aksi saat tombol kategori ditekan
bot.action(/category_(.+)/, async (ctx) => {
    const categoryId = ctx.match[1];
    await ctx.answerCbQuery();
    try {
        await ctx.editMessageText('⏳ Mencari produk...');
        const response = await axios.get(`${API_BASE_URL}/product/category/${categoryId}`);
        if (response.data && response.data.success) {
            const products = response.data.data;

            // Simpan detail produk ke cache untuk digunakan nanti saat membuat invoice
            products.forEach(p => productCache.set(p.id.toString(), p));

            if (products.length === 0) {
                await ctx.editMessageText('ℹ️ Maaf, tidak ada produk di kategori ini.', Markup.inlineKeyboard([Markup.button.callback('⬅️ Kembali', 'back_to_categories')]));
                return;
            }
            const productButtons = products.map(product => {
                const priceFormatted = `Rp ${product.price.toLocaleString('id-ID')}`;
                return Markup.button.callback(`${product.name} (${priceFormatted})`, `product_${product.id}`);
            });
            productButtons.push(Markup.button.callback('⬅️ Kembali ke Kategori', 'back_to_categories'));
            const keyboard = Markup.inlineKeyboard(productButtons, { columns: 1 });
            await ctx.editMessageText('Silakan pilih produk yang Anda inginkan:', keyboard);
        } else {
            await ctx.editMessageText('❌ Gagal mengambil data produk.');
        }
    } catch (error) {
        console.error("Error fetching products:", error);
        await ctx.editMessageText('Terjadi kesalahan saat menghubungi server.');
    }
});

// --- PERUBAHAN UTAMA: Handler saat produk dipilih ---
// ... (kode di atasnya tetap sama)

// --- PERUBAHAN UTAMA: Handler saat produk dipilih sekarang membuat order via API ---
bot.action(/product_(.+)/, async (ctx) => {
    const productId = ctx.match[1];
    const telegramId = ctx.from.id;
    await ctx.answerCbQuery();

    try {
        // Beri tahu pengguna bahwa pesanan sedang dibuat
        await ctx.editMessageText('⏳ Membuat pesanan Anda, harap tunggu...');

        const orderData = {
            telegram_id: telegramId,
            product_id: productId
        };

        const response = await axios.post(`${API_BASE_URL}/order/create`, orderData);
    
        if (response.data && response.data.success) {
            const orderDetails = response.data.data; // Misal: { invoice_id, product_name, total_price, qris_image_url }

            // Hapus pesan "Membuat pesanan..."
            await ctx.deleteMessage();

            const priceFormatted = `Rp ${orderDetails.amount.toLocaleString('id-ID')}`;

            const invoiceText = `🧾 **INVOICE PEMBAYARAN**\n\n` +
                                `**ID Pesanan:** \`${orderDetails.external_id}\`\n` +
                                `**Produk:** ${response.data.product?.name || '-'}\n` +
                                `**Total Bayar:** *${priceFormatted}*\n\n` +
                                `Silahkan lakukan pembayaran dengan klik tombol "Bayar" dibawah ini
Setelah melakukan pembayaran, produk otomatis akan di kirim ke akun anda.`;

            const keyboard = Markup.inlineKeyboard([
                Markup.button.url(`💳 Bayar ${priceFormatted}`, orderDetails.invoice_url),
                Markup.button.callback('❌ Batalkan', `cancel_${orderDetails.external_id}`)
            ],{ columns: 1 });
            // Kirim pesan invoice dengan tombol konfirmasi
            await ctx.reply(invoiceText, {
                parse_mode: 'Markdown',
                
                ...keyboard
            });


        } else {
            // Jika success: false dari API
            await ctx.editMessageText(response.data.message || 'Gagal membuat pesanan. Stok mungkin habis.');
        }

    } catch (error) {
        // Tangani jika API error (misal: 500, 404, atau tidak terhubung)
        console.error("API Error at /order/create:", error.message);
        await ctx.editMessageText(
            '❌ Maaf, terjadi kesalahan saat membuat pesanan Anda. Silakan coba lagi nanti.', 
            Markup.inlineKeyboard([
                Markup.button.callback('⬅️ Kembali ke Kategori', 'back_to_categories')
            ])
        );
    }
});



// --- HANDLER BARU: Untuk tombol konfirmasi pembayaran ---
bot.action(/confirm_(.+)/, async (ctx) => {
    const invoiceId = ctx.match[1];
    console.log(ctx.match);
    await ctx.answerCbQuery('Sedang mengecek pembayaran...');

    // Simulasi pengecekan
    await ctx.reply(
        `⏳ Sedang memverifikasi pembayaran untuk \`${invoiceId}\`...\n\nHarap tunggu sebentar.`,
        { parse_mode: 'Markdown' }
    );

    // Simulasi hasil setelah beberapa detik
    setTimeout(async () => {
        // Ganti caption pesan yang ada
        await ctx.editMessageCaption(
            `✅ **Pembayaran Berhasil!**\n\n` +
            `Terima kasih! Pesanan Anda untuk \`${invoiceId}\` sedang diproses.`,
            {
                parse_mode: 'Markdown',
                ...Markup.inlineKeyboard([
                    Markup.button.callback('🛒 Beli Lagi', 'back_to_categories')
                ])
            }
        );
    }, 3000); // Tunggu 3 detik
});

// --- HANDLER BARU: Untuk tombol batalkan pesanan ---
bot.action(/cancel_(.+)/, async (ctx) => {
    const invoiceId = ctx.match[1]; // Dapatkan invoice_id dari tombol
    await ctx.answerCbQuery();
    await ctx.deleteMessage();

    try {
        // Beri feedback ke user bahwa proses sedang berjalan
        await ctx.replyWithChatAction('typing');
        const response = await axios.get(`${API_BASE_URL}/order/cancel/${invoiceId}`);
        
        // Asumsi: API akan merespon dengan { success: true } jika berhasil
        if (response.data && response.data.success) {
            // Hapus pesan invoice jika pembatalan di server berhasil
            await ctx.deleteMessage(); 
            await ctx.reply('Pesanan Anda telah berhasil dibatalkan.');
            
            // Tampilkan kembali daftar kategori agar user bisa belanja lagi
            await showCategories(ctx, false); 
        } else {
            // Jika API merespon success: false (misal, pesanan sudah dibayar)
            await ctx.reply(response.data.message || 'Gagal membatalkan pesanan. Mungkin sudah dibayar atau sudah di selesaikan');
        }

    } catch (error) {
        console.error(`API Error at /order/cancel/${invoiceId}:`, error.message);
        
        // Beri tahu user jika ada masalah koneksi/server
        await ctx.reply('Maaf, terjadi kesalahan pada server saat mencoba membatalkan pesanan. Silakan coba lagi.');
        
        // Kita tidak menghapus pesan invoice agar user tahu pesanan mana yang gagal dibatalkan
    }
});


// Handler untuk tombol "Kembali ke Kategori"
bot.action('back_to_categories', async (ctx) => {
    await ctx.answerCbQuery();
    await showCategories(ctx, true);
});


// --- Menjalankan Bot ---
bot.launch();
console.log('Bot Telegram sedang berjalan dengan fitur invoice QRIS...');

// Menangani proses berhenti dengan baik
process.once('SIGINT', () => bot.stop('SIGINT'));
process.once('SIGTERM', () => bot.stop('SIGTERM'));