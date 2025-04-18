import { useCustomMutation, useApiUrl } from '@refinedev/core'
import { FormInstance } from 'antd'
import { useCallback } from 'react'
import { useQueryClient } from '@tanstack/react-query'

const useSave = ({ form }: { form: FormInstance }) => {
	const apiUrl = useApiUrl()
	const mutation = useCustomMutation()
	const { mutate } = mutation
	const queryClient = useQueryClient()

	const handleSave = useCallback(() => {
		form.validateFields().then((values) => {
			mutate(
				{
					url: `${apiUrl}/options`,
					method: 'post',
					values,
					successNotification: (data, values) => {
						return {
							message: data?.data?.message || '儲存成功',
							type: 'success',
						}
					},
					errorNotification: (data, values) => {
						return {
							message: data?.data?.message || '儲存失敗',
							type: 'error',
						}
					},
				},
				{
					onSuccess: () => {
						queryClient.invalidateQueries(['get_options'])
					},
				},
			)
		})
	}, [form])

	return {
		handleSave,
		mutation,
	}
}

export default useSave
