import { ApiClient } from '@/lib/apiClient';
import { Link, useLoaderData } from 'react-router-dom';

export async function categoriesLoader() {
    const result = await ApiClient.showCategories();
    if (result._tag === 'Success') {
        return result.data;
    }
    throw new Error('Failed to load categories');
}

export function CategoriesPage() {
    const data = useLoaderData();

    return (
        <div className="container mx-auto px-4 py-8">
            <h1 className="mb-8 text-3xl font-bold">Categories</h1>

            <div className="grid grid-cols-2 gap-6 md:grid-cols-4">
                {data.categories.map((category) => (
                    <Link
                        key={category.id}
                        to={`/browse/categories/${category.id}`}
                        className="bg-card rounded-lg p-6 shadow transition-shadow hover:shadow-lg"
                    >
                        <h2 className="text-xl font-semibold">
                            {category.name}
                        </h2>
                        {category.description && (
                            <p className="text-muted mt-2">
                                {category.description}
                            </p>
                        )}
                    </Link>
                ))}
            </div>
        </div>
    );
}
