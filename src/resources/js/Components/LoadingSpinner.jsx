import React from 'react';

/**
 * Reusable loading spinner component
 *
 * @param {Object} props
 * @param {string} props.size - Size of spinner: 'sm', 'md', 'lg' (default: 'md')
 * @param {string} props.className - Additional CSS classes
 * @param {string} props.color - Color variant: 'primary', 'white', 'gray' (default: 'primary')
 */
export default function LoadingSpinner({
    size = 'md',
    className = '',
    color = 'primary'
}) {
    const sizeClasses = {
        sm: 'w-4 h-4',
        md: 'w-6 h-6',
        lg: 'w-8 h-8',
    };

    const colorClasses = {
        primary: 'text-blue-600 dark:text-blue-400',
        white: 'text-white',
        gray: 'text-gray-600 dark:text-gray-400',
    };

    return (
        <svg
            className={`animate-spin ${sizeClasses[size]} ${colorClasses[color]} ${className}`}
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
        >
            <circle
                className="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                strokeWidth="4"
            />
            <path
                className="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            />
        </svg>
    );
}

