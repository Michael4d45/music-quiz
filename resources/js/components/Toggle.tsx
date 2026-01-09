import { cn } from '@/lib/utils';
import { InputHTMLAttributes, useId, useState } from 'react';

interface ToggleProps extends Omit<
    InputHTMLAttributes<HTMLInputElement>,
    'type'
> {
    label?: string;
    labelClassName?: string;
    containerClassName?: string;
}

export default function Toggle({
    id,
    label,
    labelClassName,
    containerClassName,
    className,
    checked,
    defaultChecked,
    onChange,
    ...props
}: ToggleProps) {
    const generatedId = useId();
    const toggleId = id || generatedId;
    const [internalChecked, setInternalChecked] = useState(
        defaultChecked ?? false,
    );
    const isChecked = checked !== undefined ? checked : internalChecked;

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        if (checked === undefined) {
            setInternalChecked(e.target.checked);
        }
        onChange?.(e);
    };

    return (
        <div className={cn('flex items-center', containerClassName)}>
            <input
                type="checkbox"
                id={toggleId}
                checked={isChecked}
                onChange={handleChange}
                className="sr-only"
                {...props}
            />
            <label
                htmlFor={toggleId}
                className={cn(
                    'relative inline-flex h-6 w-11 cursor-pointer items-center rounded-full transition-colors focus-within:ring-2 focus-within:ring-offset-2 focus-within:outline-none',
                    'focus-within:ring-(--primary)',
                    isChecked ? 'bg-(--primary)' : 'bg-(--secondary-border)',
                    props.disabled && 'cursor-not-allowed opacity-50',
                    className,
                )}
            >
                <span
                    className={cn(
                        'bg-card inline-block h-4 w-4 transform rounded-full transition-transform',
                        isChecked ? 'translate-x-6' : 'translate-x-1',
                    )}
                />
            </label>
            {label && (
                <label
                    htmlFor={toggleId}
                    className={cn(
                        'text-secondary ml-3 cursor-pointer text-sm font-medium',
                        labelClassName,
                    )}
                >
                    {label}
                </label>
            )}
        </div>
    );
}
