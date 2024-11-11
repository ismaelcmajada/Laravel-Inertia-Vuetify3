/**
 * Rule validate required
 * @param {any} v the given value to validate
 * @param {string} label
 * @returns validate
 */
export function ruleRequired(v, label = null) {
  if (Array.isArray(v)) {
    return v.length !== 0 || `El ${label ?? "campo"} es obligatorio`
  }
  return !!v || `El ${label ?? "campo"} es obligatorio`
}
/**
 * Rule validate min length
 * @param {any} v the given value to validate
 * @param {number} length min length to check
 * @param {string} label
 * @returns validate
 */
export function ruleMinLength(v, length, label = null) {
  return (
    String(v).length >= length ||
    `El ${label ?? "campo"} debe tener mínimo ${length} caracteres`
  )
}
/**
 * Rule validate max length
 * @param {any} v the given value to validate
 * @param {number} length max length to check
 * @param {string} label
 * @returns validate
 */
export function ruleMaxLength(v, length, label = null) {
  return (
    String(v).length <= length ||
    `El ${label ?? "campo"} debe tener máximo ${length} caracteres`
  )
}
/**
 * Rule validate email
 * @param {any} v the given value to validate
 * @param {string} label
 * @returns validate
 */
export function ruleEmail(v, label = null) {
  return (
    !v ||
    /^[a-zA-Z0-9-_.]+@[a-zA-Z0-9-_.]+(\.\w{2,3})+$/.test(String(v)) ||
    `El ${label ?? "campo"} debe ser un email válido`
  )
}
/**
 * Rule validate DNI
 * @param {any} v the given value to validate
 * @param {string} label
 * @returns validate
 */
export function ruleDNI(v, label = null) {
  return (
    !v ||
    /^[0-9]{8}[A-Za-z]$/.test(String(v)) ||
    `El ${label ?? "campo"} debe ser un DNI válido`
  )
}
/**
 * Rule validate phone number
 * @param {any} v the given value to validate
 * @param {string} label
 * @returns validate
 */
export function ruleTelephone(v, label = null) {
  return (
    !v ||
    /^((\+\d{1,3}(-| )?\(?\d\)?(-| )?\d{1,5})|(\(?\d{2,6}\)?))(-| )?(\d{3,4})(-| )?(\d{4})(( x| ext)\d{1,5}){0,1}$/.test(
      String(v)
    ) ||
    `El ${label ?? "campo"} debe ser un teléfono válido`
  )
}
/**
 * Rule validate number less than
 * @param {any} v the given value to validate
 * @param {any} targetValue the target value to check againt
 * @param {string} label
 * @returns validate
 */
export function ruleLessThan(v, targetValue, label = null) {
  return (
    !v ||
    !targetValue ||
    parseFloat(v) < parseFloat(targetValue) ||
    `El ${label ?? "campo"} debe ser menor que ${targetValue}`
  )
}
/**
 * Rule validate number greater than
 * @param {any} v the given value to validate
 * @param {any} targetValue the target value to check againt
 * @param {string} label
 * @returns validate
 */
export function ruleGreaterThan(v, targetValue, label = null) {
  return (
    !v ||
    !targetValue ||
    parseFloat(v) > parseFloat(targetValue) ||
    `El ${label ?? "campo"} debe ser mayor que ${targetValue}`
  )
}
/**
 * Rule validate integer number
 * @param {any} v the given value to validate
 * @param {string} label
 * @returns validate
 */
export function ruleNumber(v, label = null) {
  return (
    Number.isInteger(Number(v)) || `El ${label ?? "campo"} debe ser un número`
  )
}

/**
 * Rule validate float number
 * @param {any} v the given value to validate
 * @param {string} label
 * @returns validate
 */
export function ruleFloat(v, label = null) {
  const floatRegex = /^-?\d*(\.\d+)?$/
  return (
    !v ||
    floatRegex.test(v) ||
    `${label ?? "El campo"} debe ser un número decimal`
  )
}

export const getFieldRules = (v, field, ty) => {
  const rules = []
  if (field.rules?.required) {
    rules.push(ruleRequired(v))
  }
  switch (field.type) {
    case "string":
      if (field.rules?.maxLength) {
        rules.push(ruleMaxLength(v, field.rules.maxLength))
      }
      if (field.rules?.minLength) {
        rules.push(ruleMinLength(v, field.rules.minLength))
      }
      break
    case "text":
      if (field.rules?.maxLength) {
        rules.push(ruleMaxLength(v, field.rules.maxLength))
      }
      if (field.rules?.minLength) {
        rules.push(ruleMinLength(v, field.rules.minLength))
      }
      break
    case "number":
      rules.push(ruleNumber(v))
      if (field.rules?.min) {
        rules.push(ruleGreaterThan(v, field.rules.min))
      }
      if (field.rules?.max) {
        rules.push(ruleLessThan(v, field.rules.max))
      }
      break
    case "float":
      rules.push(ruleFloat(v))
      if (field.rules?.min) {
        rules.push(ruleGreaterThan(v, field.rules.min))
      }
      if (field.rules?.max) {
        rules.push(ruleLessThan(v, field.rules.max))
      }
      break
    case "email":
      rules.push(ruleEmail(v))
      if (field.rules?.maxLength) {
        rules.push(ruleMaxLength(v, field.rules.maxLength))
      }
      if (field.rules?.minLength) {
        rules.push(ruleMinLength(v, field.rules.minLength))
      }
      break
    case "dni":
      rules.push(ruleDNI(v))
      break
    case "telephone":
      rules.push(ruleTelephone(v))
      break
    default:
      break
  }
  return rules
}
