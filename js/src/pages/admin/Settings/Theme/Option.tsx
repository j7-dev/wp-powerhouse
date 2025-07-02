import { cn } from 'antd-toolkit'
import { Form, FormInstance } from 'antd'

const Option = ({ theme, form }: { theme: string; form: FormInstance }) => {
	const watchTheme =
		Form.useWatch(['powerhouse_settings', 'theme'], form) || 'power'
	return (
		<div className="relative cursor-pointer ">
			<div
				className={cn(
					'overflow-hidden border-base-content/20 hover:border-base-content/40 rounded-lg border outline outline-2 outline-offset-2 outline-transparent',
					watchTheme === theme && 'outline outline-4 outline-yellow-300',
				)}
				onClick={() => {
					form.setFieldValue(['powerhouse_settings', 'theme'], theme)
				}}
			>
				<div
					className="bg-base-100 text-base-content w-full cursor-pointer font-sans"
					data-theme={theme}
				>
					<div className="grid grid-cols-5 grid-rows-3">
						<div className="bg-base-200 col-start-1 row-span-2 row-start-1"></div>{' '}
						<div className="bg-base-300 col-start-1 row-start-3"></div>{' '}
						<div className="bg-base-100 col-span-4 col-start-2 row-span-3 row-start-1 flex flex-col gap-1 p-2">
							<div className="font-bold">{theme}</div>{' '}
							<div className="flex flex-wrap gap-1">
								<div className="bg-primary flex aspect-square w-5 items-center justify-center rounded lg:w-6">
									<div className="text-primary-content text-sm font-bold">
										A
									</div>
								</div>{' '}
								<div className="bg-secondary flex aspect-square w-5 items-center justify-center rounded lg:w-6">
									<div className="text-secondary-content text-sm font-bold">
										A
									</div>
								</div>{' '}
								<div className="bg-accent flex aspect-square w-5 items-center justify-center rounded lg:w-6">
									<div className="text-accent-content text-sm font-bold">A</div>
								</div>{' '}
								<div className="bg-neutral flex aspect-square w-5 items-center justify-center rounded lg:w-6">
									<div className="text-neutral-content text-sm font-bold">
										A
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			{watchTheme === theme && (
				<div className="bg-white absolute -top-2 -right-2 z-30 w-6 h-6 -1 rounded-full flex items-center justify-center">
					<svg
						viewBox="0 0 20 20"
						xmlns="http://www.w3.org/2000/svg"
						fill="none"
						className="w-5 h-5 [&_path]:fill-yellow-300"
					>
						<g strokeWidth="0"></g>
						<g strokeLinecap="round" strokeLinejoin="round"></g>
						<g>
							{' '}
							<path
								fill="#000000"
								fillRule="evenodd"
								d="M3 10a7 7 0 019.307-6.611 1 1 0 00.658-1.889 9 9 0 105.98 7.501 1 1 0 00-1.988.22A7 7 0 113 10zm14.75-5.338a1 1 0 00-1.5-1.324l-6.435 7.28-3.183-2.593a1 1 0 00-1.264 1.55l3.929 3.2a1 1 0 001.38-.113l7.072-8z"
							></path>{' '}
						</g>
					</svg>
				</div>
			)}
		</div>
	)
}

export default Option
