/* eslint-disable quote-props */
import { Refine } from '@refinedev/core'

import {
	ThemedLayoutV2,
	ThemedSiderV2,
	ErrorComponent,
	useNotificationProvider,
} from '@refinedev/antd'
import '@refinedev/antd/dist/reset.css'
import routerBindings, {
	UnsavedChangesNotifier,
	NavigateToResource,
} from '@refinedev/react-router-v6'
import { Settings, LicenseCode } from '@/pages/admin'
import { HashRouter, Outlet, Route, Routes } from 'react-router-dom'
import { resources } from '@/resources'
import { ConfigProvider } from 'antd'
import { ReactQueryDevtools } from '@tanstack/react-query-devtools'
import {
	dataProvider,
	useBunny,
	MediaLibraryIndicator,
} from 'antd-toolkit/refine'
import { env } from '@/utils'
import axios, { AxiosInstance } from 'axios'
import 'antd-toolkit/style.css'

// 如果 是 vite dev 才 import scss
// 現在執行 yarn watch-css:admin 就有 css
// if (import.meta.env.DEV) {
// 	import('@/assets/scss/admin.scss')
// 	import('@/assets/scss/front.scss')
// }

function App() {
	const { bunny_data_provider_result } = useBunny()
	const { KEBAB, API_URL, NONCE } = env

	const axiosInstance: AxiosInstance = axios.create({
		timeout: 30000,
		headers: {
			// @ts-ignore
			'X-WP-Nonce': NONCE,
			'Content-Type': 'application/json',
		},
	})

	return (
		<HashRouter>
			<Refine
				dataProvider={{
					default: dataProvider(`${API_URL}/v2/powerhouse`, axiosInstance),
					'wp-rest': dataProvider(`${API_URL}/wp/v2`, axiosInstance),
					'wc-rest': dataProvider(`${API_URL}/wc/v3`, axiosInstance),
					'wc-store': dataProvider(`${API_URL}/wc/store/v1`, axiosInstance),
					'bunny-stream': bunny_data_provider_result,
				}}
				notificationProvider={useNotificationProvider}
				routerProvider={routerBindings}
				resources={resources}
				options={{
					syncWithLocation: false,
					warnWhenUnsavedChanges: true,
					projectId: 'powerhouse',
					reactQuery: {
						clientConfig: {
							defaultOptions: {
								queries: {
									staleTime: 1000 * 60 * 10,
									cacheTime: 1000 * 60 * 10,
									retry: 0,
								},
							},
						},
					},
				}}
			>
				<Routes>
					<Route
						element={
							<ConfigProvider
								theme={{
									components: {
										Collapse: {
											contentPadding: '8px 8px',
										},
									},
								}}
							>
								<ThemedLayoutV2
									Sider={(props) => <ThemedSiderV2 {...props} fixed />}
									Title={({ collapsed }) => null}
								>
									<Outlet />
									<MediaLibraryIndicator />
								</ThemedLayoutV2>
							</ConfigProvider>
						}
					>
						<Route index element={<NavigateToResource resource="settings" />} />
						<Route path="settings" element={<Settings />} />
						<Route path="license-code" element={<LicenseCode />} />
						<Route path="*" element={<ErrorComponent />} />
					</Route>
				</Routes>
				<UnsavedChangesNotifier />
				<ReactQueryDevtools initialIsOpen={false} />
			</Refine>
		</HashRouter>
	)
}

export default App
