import React from 'react'
import { useList } from '@refinedev/core'
import { TPlugin } from '@/pages/admin/Settings/Lab/types'



const usePlugins = () => {
	const result = useList<TPlugin>({
		resource: 'plugins',
	})

	return result
}

export default usePlugins