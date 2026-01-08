interface LogoProps {
    className?: string;
    width?: number;
    height?: number;
    variant?: 'auto' | 'light' | 'dark';
    fill?: boolean; // When true, fills parent container
}

export default function Logo({
    className = '',
    width = 200,
    height = 50,
    variant = 'auto',
    fill = false,
}: LogoProps) {
    const baseClasses = 'transition-all duration-200';

    const variantClasses = {
        auto: 'dark:invert dark:hue-rotate-[180deg] dark:brightness-110',
        light: '',
        dark: 'invert hue-rotate-[180deg] brightness-110',
    };

    // When fill is true, use classes to fill parent and maintain aspect ratio
    const sizeClasses = fill ? 'w-full h-full object-contain' : '';

    // Only set width/height attributes and styles when not filling
    const imgProps: React.ImgHTMLAttributes<HTMLImageElement> = {
        src: '/logo.svg',
        alt: 'Music Quiz Logo',
        className: `${baseClasses} ${variantClasses[variant]} ${sizeClasses} ${className}`.trim(),
    };

    if (!fill) {
        imgProps.width = width;
        imgProps.height = height;
        (imgProps as any).style = {
            width: `${width}px`,
            height: `${height}px`,
        };
    }

    return <img {...imgProps} />;
}
