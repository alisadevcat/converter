import { Head } from "@inertiajs/react";
import SimpleLayout from "@/Layouts/SimpleLayout";
import CurrencyConverter from "@/Pages/Converter/Partials/CurrencyConverter";

export default function Converter({
    currencies = [],
    converterResult = null,
    converterError = null,
}) {
    return (
        <SimpleLayout>
            <Head title="Converter" />
                            <div className="flex flex-col items-center justify-center text-center">
                                <div className="max-w-3xl mx-auto px-6">
                    <h1 className="text-4xl font-bold text-black mb-6">
                                        Welcome to Currency Converter
                                    </h1>
                    <p className="text-lg text-black/70 mb-8">
                                        Convert currencies with real-time
                                        exchange rates. Our system automatically
                                        updates exchange rates daily from
                                        reliable sources, ensuring you always
                                        have access to the latest conversion
                                        rates.
                                    </p>

                                    {/* Currency Converter */}
                                    <div className="mb-8">
                                        <CurrencyConverter
                                            currencies={currencies}
                                            result={converterResult}
                                            error={converterError}
                                        />
                                    </div>
                                </div>
                            </div>
        </SimpleLayout>
    );
}
