export function formatCurrency(amount) {
    return new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "IDR",
        currencyDisplay: "narrowSymbol",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
}

export function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}
export function stripTags(html) {
    // Menghapus semua tag HTML dari teks yang diberikan
    return html.replace(/<[^>]*>?/gm, "");
}
export function imageUrl(string) {
    const urlPattern = /^(http|https):\/\//i;
    
    // Check if the string matches the regular expression
    if (urlPattern.test(string)) {
        return string;
    } else {
        return '/storage/' + string;
    }
}
