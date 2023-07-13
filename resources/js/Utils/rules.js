export default {
  /**
   * Rule validate required
   * @param {any} v the given value to validate
   * @param {string} label
   * @returns validate
   */
  ruleRequired: (v, label = null) => {
    if (Array.isArray(v)) {
      return v.length !== 0 || `El ${label ?? "campo"} es obligatorio`
    }
    return !!v || `El ${label ?? "campo"} es obligatorio`
  },
  /**
   * Rule validate min length
   * @param {any} v the given value to validate
   * @param {number} length min length to check
   * @param {string} label
   * @returns validate
   */
  ruleMinLength: (v, length, label = null) => {
    return (
      String(v).length >= length ||
      `El ${label ?? "campo"} debe tener mínimo ${length} caracteres`
    )
  },
  /**
   * Rule validate max length
   * @param {any} v the given value to validate
   * @param {number} length max length to check
   * @param {string} label
   * @returns validate
   */
  ruleMaxLength: (v, length, label = null) => {
    return (
      String(v).length <= length ||
      `El ${label ?? "campo"} debe tener máximo ${length} caracteres`
    )
  },
  /**
   * Rule validate email
   * @param {any} v the given value to validate
   * @param {string} label
   * @returns validate
   */
  ruleEmail: (v, label = null) => {
    return (
      !v ||
      /^[a-zA-Z0-9-_.]+@[a-zA-Z0-9-_.]+(\.\w{2,3})+$/.test(String(v)) ||
      `El ${label ?? "campo"} debe ser un email válido`
    )
  },
  /**
   * Rule validate DNI
   * @param {any} v the given value to validate
   * @param {string} label
   * @returns validate
   */
  ruleDNI: (v, label = null) => {
    return (
      !v ||
      /^[0-9]{8}[A-Za-z]$/.test(String(v)) ||
      `El ${label ?? "campo"} debe ser un DNI válido`
    )
  },
  /**
   * Rule validate phone number
   * @param {any} v the given value to validate
   * @param {string} label
   * @returns validate
   */
  ruleTelephone: (v, label = null) => {
    return (
      !v ||
      /^((\+\d{1,3}(-| )?\(?\d\)?(-| )?\d{1,5})|(\(?\d{2,6}\)?))(-| )?(\d{3,4})(-| )?(\d{4})(( x| ext)\d{1,5}){0,1}$/.test(
        String(v)
      ) ||
      `El ${label ?? "campo"} debe ser un teléfono válido`
    )
  },
  /**
   * Rule validate number less than
   * @param {any} v the given value to validate
   * @param {any} targetValue the target value to check againt
   * @param {string} label
   * @returns validate
   */
  ruleLessThan: (v, targetValue, label = null) => {
    return (
      !v ||
      !targetValue ||
      parseFloat(v) < parseFloat(targetValue) ||
      `El ${label ?? "campo"} debe ser menor que ${targetValue}`
    )
  },
  /**
   * Rule validate number greater than
   * @param {any} v the given value to validate
   * @param {any} targetValue the target value to check againt
   * @param {string} label
   * @returns validate
   */
  ruleGreaterThan: (v, targetValue, label = null) => {
    return (
      !v ||
      !targetValue ||
      parseFloat(v) > parseFloat(targetValue) ||
      `El ${label ?? "campo"} debe ser mayor que ${targetValue}`
    )
  },
  /**
   * Rule validate integer number
   * @param {any} v the given value to validate
   * @param {string} label
   * @returns validate
   */
  ruleNumber: (v, label = null) => {
    return (
      Number.isInteger(Number(v)) || `El ${label ?? "campo"} debe ser un número`
    )
  },
}
