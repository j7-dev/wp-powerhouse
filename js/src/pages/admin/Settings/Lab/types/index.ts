export type TPlugin = {
	key: string // 唯一的路徑
	name: string
	title: string
	plugin_uri: string
	version: string
	description: string
	author: string
	author_uri: string
	text_domain: string
	domain_path: string
	network: boolean
	requires_wp: string
	requires_php: string
	update_uri: string
	requires_plugins: string
	author_name: string
	is_active: boolean
}

export type TApiBoosterRule = {
	key: string
	enable: 'yes' | 'no'
	name: string
	rules: string
	plugins: string[]
}