import { oklch, parse, Oklch, formatHex } from 'culori'
/**
 * 將 oklch string 轉為 hex
 * @param colorString  oklch string
 * @returns
 */
export function oklchToHex(colorString: string | undefined) {
	if (!colorString) return '#000000'
	// value 是 oklch string, 拆成 l,c,h
	const [l, c, h] = colorString.split(' ')
	const oklchColor = oklch({
		l: parseFloat(l) / 100, // 將百分比轉為 0-1
		c: c,
		h: h,
	} as any)
	return formatHex(oklchColor)
}

/**
 * 將 hex 轉為 oklch string
 * @param hexColor hex string
 * @returns oklch string
 */
export function hexToOklch(hexColor: string) {
	try {
		const color = parse(hexColor)
		const { l: rawL, c: rawC, h: rawH } = oklch(color) as Oklch
		const l = Number.isNaN(rawL) ? 0 : rawL || 0
		const c = Number.isNaN(rawC) ? 0 : rawC || 0
		const h = Number.isNaN(rawH) ? 0 : rawH || 0
		return `${l * 100}% ${c} ${h}`
	} catch (error) {
		console.error('hexToOklch error:', error)
		return '0% 0 0'
	}
}

export function getStyle(theme: { [key: string]: string }) {
	let style = '[data-theme=custom] {'

	Object.entries(theme).forEach(([key, value]) => {
		style += `${key}: ${value};`
	})

	style += `}`
	return style
}
