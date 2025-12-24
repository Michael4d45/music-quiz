import Layout from "@/Layout";

export default function Home() {
    return (
        <div className="flex flex-col items-center justify-center h-screen">
            <h1>Welcome</h1>
            <p>This is the welcome page</p>
        </div>
    );
}

Home.layout = (page: React.ReactNode) => <Layout>{page}</Layout>;