import { useEffect } from 'react'
import { useCustom, useApiUrl } from '@refinedev/core'
import { FormInstance } from 'antd'
import { TOptions } from '../types'

type TOptionResponse = {
	code: string
	data: TOptions
	message: string
}

const useOptions = ({ form }: { form: FormInstance }) => {
	const apiUrl = useApiUrl()
	const result = useCustom<TOptionResponse>({
		url: `${apiUrl}/options`,
		method: 'get',
		queryOptions: {
			queryKey: ['get_options'],
		},
	})

	const { isSuccess } = result
	useEffect(() => {
		if (isSuccess) {
			const values = result.data?.data?.data
			form.setFieldsValue(values)
		}
	}, [isSuccess])

	return result
}

export default useOptions
