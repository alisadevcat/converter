import { forwardRef } from 'react';

export default forwardRef(function Select(
    { className = '', children, ...props },
    ref,
) {
    return (
        <select
            {...props}
            className={
                'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white ' +
                className
            }
            ref={ref}
        >
            {children}
        </select>
    );
});

