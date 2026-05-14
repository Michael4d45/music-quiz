import { fetchMyPlaylists } from '@/features/playlists/api';
import type { MyPlaylistsResponseData } from '@/schemas/App/Data/Responses/MyPlaylistsResponseData';

export async function myPlaylistsLoader(): Promise<MyPlaylistsResponseData> {
    const result = await fetchMyPlaylists();
    if (result._tag === 'Success') {
        return result.data;
    }
    return { playlists: [] };
}
