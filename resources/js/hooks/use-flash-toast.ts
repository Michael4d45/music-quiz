import { usePage } from '@inertiajs/react';
import { useEffect } from 'react';
import toast from 'react-hot-toast';

export function useFlashToast() {
    const { flash } = usePage<Props.SharedProps>().props;

    useEffect(() => {
        if (!flash) {
            return;
        }

        if (flash.success) {
            const message = typeof flash.success === 'string' ? flash.success : 'Operation completed successfully!';
            toast.success(message);
        }

        if (flash.error) {
            toast.error(flash.error);
        }

        if (flash.info) {
            toast(flash.info, {
                icon: 'ℹ️',
            });
        }

        if (flash.warning) {
            toast(flash.warning, {
                icon: '⚠️',
            });
        }
    }, [flash]);
}
