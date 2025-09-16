/**
 * Utilidades para manejo de números telefónicos internacionales
 */

export const COUNTRY_CODES = {
  'PE': { 
    code: '+51', 
    name: 'Perú', 
    flag: '🇵🇪',
    flagClass: 'flag-pe',
    flagSvg: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMTUiIHZpZXdCb3g9IjAgMCAyMCAxNSIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjIwIiBoZWlnaHQ9IjUiIGZpbGw9IiNEOTEwMjMiLz4KPHJlY3QgeT0iNSIgd2lkdGg9IjIwIiBoZWlnaHQ9IjUiIGZpbGw9IiNGRkZGRkYiLz4KPHJlY3QgeT0iMTAiIHdpZHRoPSIyMCIgaGVpZ2h0PSI1IiBmaWxsPSIjRDkxMDIzIi8+Cjwvc3ZnPgo='
  },
  'CO': { 
    code: '+57', 
    name: 'Colombia', 
    flag: '🇨🇴',
    flagClass: 'flag-co',
    flagSvg: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMTUiIHZpZXdCb3g9IjAgMCAyMCAxNSIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjIwIiBoZWlnaHQ9IjcuNSIgZmlsbD0iI0ZGRERFMCIvPgo8cmVjdCB5PSI3LjUiIHdpZHRoPSIyMCIgaGVpZ2h0PSIzLjc1IiBmaWxsPSIjMDAzOEEzIi8+CjxyZWN0IHk9IjExLjI1IiB3aWR0aD0iMjAiIGhlaWdodD0iMy43NSIgZmlsbD0iI0NEMTEyNyIvPgo8L3N2Zz4K'
  },
  'AR': { 
    code: '+54', 
    name: 'Argentina', 
    flag: '🇦🇷',
    flagClass: 'flag-ar',
    flagSvg: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMTUiIHZpZXdCb3g9IjAgMCAyMCAxNSIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjIwIiBoZWlnaHQ9IjUiIGZpbGw9IiM3OEI5RjQiLz4KPHJlY3QgeT0iNSIgd2lkdGg9IjIwIiBoZWlnaHQ9IjUiIGZpbGw9IiNGRkZGRkYiLz4KPHJlY3QgeT0iMTAiIHdpZHRoPSIyMCIgaGVpZ2h0PSI1IiBmaWxsPSIjNzhCOUY0Ii8+Cjwvc3ZnPgo='
  },
  'CL': { 
    code: '+56', 
    name: 'Chile', 
    flag: '🇨🇱',
    flagClass: 'flag-cl',
    flagSvg: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMTUiIHZpZXdCb3g9IjAgMCAyMCAxNSIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjIwIiBoZWlnaHQ9IjciLjUiIGZpbGw9IiNGRkZGRkYiLz4KPHJlY3QgeT0iNy41IiB3aWR0aD0iMjAiIGhlaWdodD0iNy41IiBmaWxsPSIjRkYwMDMzIi8+CjxyZWN0IHdpZHRoPSI4IiBoZWlnaHQ9IjcuNSIgZmlsbD0iIzAwMDBGRiIvPgo8L3N2Zz4K'
  },
  'BO': { 
    code: '+591', 
    name: 'Bolivia', 
    flag: '🇧🇴',
    flagClass: 'flag-bo',
    flagSvg: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMTUiIHZpZXdCb3g9IjAgMCAyMCAxNSIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjIwIiBoZWlnaHQ9IjUiIGZpbGw9IiNGRjAwMDAiLz4KPHJlY3QgeT0iNSIgd2lkdGg9IjIwIiBoZWlnaHQ9IjUiIGZpbGw9IiNGRkREMDAiLz4KPHJlY3QgeT0iMTAiIHdpZHRoPSIyMCIgaGVpZ2h0PSI1IiBmaWxsPSIjMDA4MDAwIi8+Cjwvc3ZnPgo='
  },
  'EC': { 
    code: '+593', 
    name: 'Ecuador', 
    flag: '🇪🇨',
    flagClass: 'flag-ec',
    flagSvg: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMTUiIHZpZXdCb3g9IjAgMCAyMCAxNSIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjIwIiBoZWlnaHQ9IjcuNSIgZmlsbD0iI0ZGRERFMCIvPgo8cmVjdCB5PSI3LjUiIHdpZHRoPSIyMCIgaGVpZ2h0PSIzLjc1IiBmaWxsPSIjMDAzOEEzIi8+CjxyZWN0IHk9IjExLjI1IiB3aWR0aD0iMjAiIGhlaWdodD0iMy43NSIgZmlsbD0iI0NEMTEyNyIvPgo8L3N2Zz4K'
  },
  'UY': { 
    code: '+598', 
    name: 'Uruguay', 
    flag: '🇺🇾',
    flagClass: 'flag-uy',
    flagSvg: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMTUiIHZpZXdCb3g9IjAgMCAyMCAxNSIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjIwIiBoZWlnaHQ9IjEuNSIgZmlsbD0iIzAwNzZCNyIvPgo8cmVjdCB5PSIxLjUiIHdpZHRoPSIyMCIgaGVpZ2h0PSIxLjUiIGZpbGw9IiNGRkZGRkYiLz4KPHJlY3QgeT0iMyIgd2lkdGg9IjIwIiBoZWlnaHQ9IjEuNSIgZmlsbD0iIzAwNzZCNyIvPgo8cmVjdCB5PSI0LjUiIHdpZHRoPSIyMCIgaGVpZ2h0PSIxLjUiIGZpbGw9IiNGRkZGRkYiLz4KPHJlY3QgeT0iNiIgd2lkdGg9IjIwIiBoZWlnaHQ9IjEuNSIgZmlsbD0iIzAwNzZCNyIvPgo8cmVjdCB5PSI3LjUiIHdpZHRoPSIyMCIgaGVpZ2h0PSIxLjUiIGZpbGw9IiNGRkZGRkYiLz4KPHJlY3QgeT0iOSIgd2lkdGg9IjIwIiBoZWlnaHQ9IjEuNSIgZmlsbD0iIzAwNzZCNyIvPgo8cmVjdCB5PSIxMC41IiB3aWR0aD0iMjAiIGhlaWdodD0iMS41IiBmaWxsPSIjRkZGRkZGIi8+CjxyZWN0IHk9IjEyIiB3aWR0aD0iMjAiIGhlaWdodD0iMS41IiBmaWxsPSIjMDA3NkI3Ii8+CjxyZWN0IHk9IjEzLjUiIHdpZHRoPSIyMCIgaGVpZ2h0PSIxLjUiIGZpbGw9IiNGRkZGRkYiLz4KPHJlY3Qgd2lkdGg9IjEwIiBoZWlnaHQ9IjkiIGZpbGw9IiNGRkZGRkYiLz4KPC9zdmc+Cg=='
  },
  'PY': { 
    code: '+595', 
    name: 'Paraguay', 
    flag: '🇵🇾',
    flagClass: 'flag-py',
    flagSvg: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMTUiIHZpZXdCb3g9IjAgMCAyMCAxNSIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjIwIiBoZWlnaHQ9IjUiIGZpbGw9IiNGRjAwMDAiLz4KPHJlY3QgeT0iNSIgd2lkdGg9IjIwIiBoZWlnaHQ9IjUiIGZpbGw9IiNGRkZGRkYiLz4KPHJlY3QgeT0iMTAiIHdpZHRoPSIyMCIgaGVpZ2h0PSI1IiBmaWxsPSIjMDA2NkNDIi8+Cjwvc3ZnPgo='
  },
  'VE': { 
    code: '+58', 
    name: 'Venezuela', 
    flag: '🇻🇪',
    flagClass: 'flag-ve',
    flagSvg: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMTUiIHZpZXdCb3g9IjAgMCAyMCAxNSIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjIwIiBoZWlnaHQ9IjUiIGZpbGw9IiNGRkREMDAiLz4KPHJlY3QgeT0iNSIgd2lkdGg9IjIwIiBoZWlnaHQ9IjUiIGZpbGw9IiMwMDMzQTAiLz4KPHJlY3QgeT0iMTAiIHdpZHRoPSIyMCIgaGVpZ2h0PSI1IiBmaWxsPSIjQ0YxMDIyIi8+Cjwvc3ZnPgo='
  },
  'US': { 
    code: '+1', 
    name: 'Estados Unidos', 
    flag: '🇺🇸',
    flagClass: 'flag-us',
    flagSvg: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMTUiIHZpZXdCb3g9IjAgMCAyMCAxNSIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjIwIiBoZWlnaHQ9IjEuMTUiIGZpbGw9IiNCMjIyMzQiLz4KPHJlY3QgeT0iMS4xNSIgd2lkdGg9IjIwIiBoZWlnaHQ9IjEuMTUiIGZpbGw9IiNGRkZGRkYiLz4KPHJlY3QgeT0iMi4zIiB3aWR0aD0iMjAiIGhlaWdodD0iMS4xNSIgZmlsbD0iI0IyMjIzNCIvPgo8cmVjdCB5PSIzLjQ1IiB3aWR0aD0iMjAiIGhlaWdodD0iMS4xNSIgZmlsbD0iI0ZGRkZGRiIvPgo8cmVjdCB5PSI0LjYiIHdpZHRoPSIyMCIgaGVpZ2h0PSIxLjE1IiBmaWxsPSIjQjIyMjM0Ii8+CjxyZWN0IHk9IjUuNzUiIHdpZHRoPSIyMCIgaGVpZ2h0PSIxLjE1IiBmaWxsPSIjRkZGRkZGIi8+CjxyZWN0IHk9IjYuOSIgd2lkdGg9IjIwIiBoZWlnaHQ9IjEuMTUiIGZpbGw9IiNCMjIyMzQiLz4KPHJlY3QgeT0iOC4wNSIgd2lkdGg9IjIwIiBoZWlnaHQ9IjEuMTUiIGZpbGw9IiNGRkZGRkYiLz4KPHJlY3QgeT0iOS4yIiB3aWR0aD0iMjAiIGhlaWdodD0iMS4xNSIgZmlsbD0iI0IyMjIzNCIvPgo8cmVjdCB5PSIxMC4zNSIgd2lkdGg9IjIwIiBoZWlnaHQ9IjEuMTUiIGZpbGw9IiNGRkZGRkYiLz4KPHJlY3QgeT0iMTEuNSIgd2lkdGg9IjIwIiBoZWlnaHQ9IjEuMTUiIGZpbGw9IiNCMjIyMzQiLz4KPHJlY3QgeT0iMTIuNjUiIHdpZHRoPSIyMCIgaGVpZ2h0PSIxLjE1IiBmaWxsPSIjRkZGRkZGIi8+CjxyZWN0IHk9IjEzLjgiIHdpZHRoPSIyMCIgaGVpZ2h0PSIxLjIiIGZpbGw9IiNCMjIyMzQiLz4KPHJlY3Qgd2lkdGg9IjgiIGhlaWdodD0iOCIgZmlsbD0iIzNDNDE0QyIvPgo8L3N2Zz4K'
  },
  'MX': { 
    code: '+52', 
    name: 'México', 
    flag: '🇲🇽',
    flagClass: 'flag-mx',
    flagSvg: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMTUiIHZpZXdCb3g9IjAgMCAyMCAxNSIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjYuNjciIGhlaWdodD0iMTUiIGZpbGw9IiMwMDY1NDciLz4KPHJlY3QgeD0iNi42NyIgd2lkdGg9IjYuNjciIGhlaWdodD0iMTUiIGZpbGw9IiNGRkZGRkYiLz4KPHJlY3QgeD0iMTMuMzMiIHdpZHRoPSI2LjY3IiBoZWlnaHQ9IjE1IiBmaWxsPSIjQ0UxMTI2Ii8+Cjwvc3ZnPgo='
  },
  'ES': { 
    code: '+34', 
    name: 'España', 
    flag: '🇪🇸',
    flagClass: 'flag-es',
    flagSvg: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMTUiIHZpZXdCb3g9IjAgMCAyMCAxNSIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjIwIiBoZWlnaHQ9IjMuNzUiIGZpbGw9IiNBQTAwMDAiLz4KPHJlY3QgeT0iMy43NSIgd2lkdGg9IjIwIiBoZWlnaHQ9IjcuNSIgZmlsbD0iI0ZGREQwMCIvPgo8cmVjdCB5PSIxMS4yNSIgd2lkdGg9IjIwIiBoZWlnaHQ9IjMuNzUiIGZpbGw9IiNBQTAwMDAiLz4KPC9zdmc+Cg=='
  }
};

/**
 * Formatear número telefónico con código de país
 * @param {string} phone - Número telefónico
 * @param {string} countryCode - Código de país (PE, CO, etc.)
 * @returns {string} - Número formateado con código de país
 */
export function formatPhoneWithCountry(phone, countryCode = 'PE') {
  if (!phone) return ''
  
  // Limpiar el número de caracteres no numéricos excepto '+'
  const cleanPhone = phone.replace(/[^\d+]/g, '')
  
  // Si ya tiene código de país, devolverlo tal como está
  if (cleanPhone.startsWith('+')) {
    return cleanPhone
  }
  
  // Si no tiene código de país, agregarlo
  const country = COUNTRY_CODES[countryCode]
  if (country) {
    return `${country.code}${cleanPhone}`
  }
  
  return cleanPhone
}

/**
 * Extraer código de país de un número telefónico
 * @param {string} phone - Número telefónico completo
 * @returns {object} - {countryCode, phoneNumber, isValid}
 */
export function parsePhoneNumber(phone) {
  if (!phone) return { countryCode: null, phoneNumber: '', isValid: false }
  
  const cleanPhone = phone.replace(/[^\d+]/g, '')
  
  // Si no empieza con +, asumir que es un número local
  if (!cleanPhone.startsWith('+')) {
    return {
      countryCode: null,
      phoneNumber: cleanPhone,
      isValid: cleanPhone.length >= 7
    }
  }
  
  // Buscar código de país que coincida
  for (const [code, data] of Object.entries(COUNTRY_CODES)) {
    if (cleanPhone.startsWith(data.code)) {
      const phoneNumber = cleanPhone.substring(data.code.length)
      return {
        countryCode: code,
        phoneNumber: phoneNumber,
        isValid: phoneNumber.length >= 7,
        fullNumber: cleanPhone
      }
    }
  }
  
  return { countryCode: null, phoneNumber: cleanPhone, isValid: false }
}

/**
 * Validar número telefónico
 * @param {string} phone - Número telefónico
 * @param {string} countryCode - Código de país esperado
 * @returns {boolean} - Si es válido
 */
export function isValidPhone(phone, countryCode = 'PE') {
  const parsed = parsePhoneNumber(phone)
  
  // Si no tiene código de país, validar como número local
  if (!parsed.countryCode && countryCode) {
    return parsed.phoneNumber.length >= 7 && parsed.phoneNumber.length <= 15
  }
  
  // Si tiene código de país, debe coincidir
  return parsed.isValid && parsed.countryCode === countryCode
}

/**
 * Obtener lista de países para selector
 * @returns {Array} - Lista de países con código, nombre y bandera
 */
export function getCountryList() {
  return Object.entries(COUNTRY_CODES).map(([countryCode, data]) => ({
    code: countryCode,          // Código del país (PE, CO, etc.) - para v-model
    phoneCode: data.code,       // Código telefónico (+51, +57, etc.) - para mostrar
    name: data.name,            // Nombre del país
    flag: data.flag,            // Bandera emoji
    flagClass: data.flagClass,  // Clase CSS para la bandera
    flagSvg: data.flagSvg       // SVG de la bandera en base64
  }))
}

/**
 * Formatear número para mostrar de forma amigable
 * @param {string} phone - Número telefónico
 * @returns {string} - Número formateado para mostrar
 */
export function formatPhoneDisplay(phone) {
  if (!phone) return ''
  
  const parsed = parsePhoneNumber(phone)
  if (parsed.countryCode && COUNTRY_CODES[parsed.countryCode]) {
    const country = COUNTRY_CODES[parsed.countryCode]
    return `${country.flag} ${country.code} ${parsed.phoneNumber}`
  }
  
  return phone
}