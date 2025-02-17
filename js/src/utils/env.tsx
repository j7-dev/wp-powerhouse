/* eslint-disable @typescript-eslint/ban-ts-comment */
// @ts-nocheck

import { simpleDecrypt } from 'antd-toolkit'

const encryptedEnv = window?.powerhouse_data?.env
export const env = simpleDecrypt(encryptedEnv)
console.log('⭐  env:', env)

export const API_URL = env?.API_URL || '/wp-json'
export const APP1_SELECTOR = env?.APP1_SELECTOR || 'powerhouse'
