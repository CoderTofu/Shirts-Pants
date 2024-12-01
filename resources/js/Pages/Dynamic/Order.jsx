import Navbar from "../../Elements/Navbar";
import { Head, useForm } from "@inertiajs/react";
import { useEffect, useRef } from "react";

export default function Order({ order }) {
    console.log(order);
    const initialRenderRef = useRef(true);
    const {
        data,
        setData,
        patch,
        delete: destroy,
        processing,
    } = useForm({
        id: order.id,
        status: order.status,
        toDelete: [],
        total: Number(order.total),
    });
    const remove = (id) => {
        if (order.products.length === data.toDelete.length + 1) {
            alert("An order must have at least 1 item.");
        } else {
            const price = order.products.find((product) => product.id === id)
                .product.price;
            console.log(price);
            setData({
                ...data,
                toDelete: [...data.toDelete, id],
                total: data.total - Number(price),
            });
        }
    };

    const update = (e) => {
        e.preventDefault();
        patch(`/order/${order.id}`);
    };

    return (
        <>
            <Navbar />
            <Head title="Order" />
            <main className="py-12 px-[200px] albert-sans">
                {/* Order ID and Time */}
                <div className="flex justify-between items-center mb-2">
                    <div className="flex items-end mb-2">
                        <h1 className="text-4xl mr-5">Order #{order.id}</h1>
                        <h4 className="text-xl text-customGray">
                            {new Date(order.date).toUTCString()}
                        </h4>
                    </div>
                    <div>
                        <button
                            onClick={update}
                            className="px-4 py-2 bg-green-600 text-white font-semibold rounded-md hover:bg-green-700 transition-colors duration-300"
                        >
                            Save Changes
                        </button>
                    </div>
                </div>

                {/* Order Details and Customer info */}
                <section className="flex justify-normal">
                    {/* Order */}
                    <div className="bg-white flex-1 border-2 border-gray-300 rounded-lg">
                        <div className="flex justify-between items-center border-b border-gray-300 py-4 px-10">
                            <div>
                                <h2 className="text-2xl">Order Details</h2>
                            </div>
                            <div className="flex items-center space-x-2">
                                <h4 className="albert-sans text-base font-light pb-1">
                                    Status:
                                </h4>
                                <select
                                    value={data.status}
                                    onChange={(e) =>
                                        setData({
                                            ...data,
                                            status: e.target.value,
                                        })
                                    }
                                    className="cursor-pointer border border-gray-300 rounded-md px-3 py-1 text-base focus:outline-none focus:ring-2 focus:ring-gray-400"
                                >
                                    <option value="To Ship">To Ship</option>
                                    <option value="Shipping">Shipping</option>
                                    <option value="Completed">Completed</option>
                                    <option value="Cancelled">Cancelled</option>
                                    <option value="Return/Refund">
                                        Return/Refund
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div>
                            {order.products.map((product, index) => {
                                if (data.toDelete.includes(product.id)) {
                                    return null;
                                }
                                return (
                                    <div
                                        className="flex justify-between px-10 items-center my-5"
                                        key={index}
                                    >
                                        {/* Product Detail */}
                                        <div className="flex items-center overflow-hidden">
                                            <img
                                                src={`/assets/products/${product.product.display_image}`}
                                                alt={product.product.name}
                                                className="w-[100px] h-[100px] object-cover "
                                            />
                                            <div className="ml-5">
                                                <h3 className="text-xl">
                                                    {product.product.name}
                                                </h3>
                                                <h4 className="text-base">
                                                    Size: {product.variant.size}
                                                </h4>
                                                <h4 className="text-sm">
                                                    Quantity: {product.quantity}
                                                </h4>
                                            </div>
                                        </div>

                                        {/* Price and action button */}
                                        <div className="flex justify-start ">
                                            <p className="ml-5 text-customGray">
                                                P{" "}
                                                {(
                                                    Number(
                                                        product.product.price
                                                    ) * product.quantity
                                                ).toFixed(2)}
                                            </p>
                                            <button
                                                onClick={() =>
                                                    remove(product.id)
                                                }
                                                className="ml-20 px-4 py-2 text-red-600 border border-red-600 rounded-md hover:bg-red-600 hover:text-white transition-colors duration-300"
                                            >
                                                {" "}
                                                Remove
                                            </button>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>

                        <div className="border-b border-gray-300 mx-8"></div>

                        <div className="py-5 px-10 flex items-center">
                            <h4 className="mr-3 text-xl font-semibold">
                                Total:{" "}
                            </h4>
                            <h3 className="text-base">
                                P {data.total.toFixed(2)}
                            </h3>
                        </div>
                    </div>

                    {/* Customer Info */}
                    <div className="bg-white border-2 border-gray-300 rounded-lg ml-2 py-4 px-7 min-w-[20vw] h-fit">
                        <div className="text-2xl mb-3">
                            <h2>Customer Info</h2>
                        </div>
                        <div>
                            <div className="mb-2">
                                <h4 className="text-sm text-customGray">
                                    Name
                                </h4>
                                <h3 className="text-lg">
                                    {order.customer.name}
                                </h3>
                            </div>
                            <div className="mb-2">
                                <h4 className="text-sm text-customGray">
                                    Email
                                </h4>
                                <h3 className="text-lg">
                                    {order.customer.email}
                                </h3>
                            </div>
                            <div className="mb-2">
                                <h4 className="text-sm text-customGray">
                                    Phone
                                </h4>
                                <h3 className="text-lg">
                                    {order.customer.phone}
                                </h3>
                            </div>
                            <div className="mb-2">
                                <h4 className="text-sm text-customGray">
                                    Shipping Address
                                </h4>
                                <h3 className="text-lg">
                                    {order.customer.address}
                                </h3>
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        </>
    );
}
