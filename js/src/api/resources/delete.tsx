import { axiosInstance as axios } from 'antd-toolkit/refine'
import { API_URL, getDataProviderUrlParams } from '@/utils'
import { TDataProvider } from '@/types'
import { AxiosRequestConfig } from 'axios'

export const deleteResource = async ({
	resource,
	dataProvider = 'wp-rest',
	pathParams = [],
	config = undefined,
}: {
	resource: string
	dataProvider?: TDataProvider
	pathParams?: string[]
	config?: AxiosRequestConfig<{ [key: string]: any }> | undefined
}) => {
	const dataProviderUrlParams = getDataProviderUrlParams(dataProvider)
	const deleteResult = await axios.delete(
		`${API_URL}/${dataProviderUrlParams}/${resource}/${pathParams.join('/')}`,
		config,
	)

	return deleteResult
}
