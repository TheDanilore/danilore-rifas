/**
 * Utilidades para manejo de números telefónicos internacionales
 */

export const COUNTRY_CODES = {
  'PE': { code: '+51', name: 'Perú', flag: '🇵🇪' },
  'CO': { code: '+57', name: 'Colombia', flag: '🇨🇴' },
  'AR': { code: '+54', name: 'Argentina', flag: '🇦🇷' },
  'CL': { code: '+56', name: 'Chile', flag: '🇨🇱' },
  'BO': { code: '+591', name: 'Bolivia', flag: '🇧🇴' },
  'EC': { code: '+593', name: 'Ecuador', flag: '🇪🇨' },
  'UY': { code: '+598', name: 'Uruguay', flag: '🇺🇾' },
  'PY': { code: '+595', name: 'Paraguay', flag: '🇵🇾' },
  'VE': { code: '+58', name: 'Venezuela', flag: '🇻🇪' },
  'US': { code: '+1', name: 'Estados Unidos', flag: '🇺🇸' },
  'MX': { code: '+52', name: 'México', flag: '🇲🇽' },
  'ES': { code: '+34', name: 'España', flag: '🇪🇸' }
}

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
  return Object.entries(COUNTRY_CODES).map(([code, data]) => ({
    code,
    ...data
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