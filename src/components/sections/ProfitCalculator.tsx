'use client';

import { useState, useMemo } from 'react';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Slider } from '@/components/ui/slider';
import { Button } from '@/components/ui/button';
import { Calculator, TrendingUp, ArrowRight } from 'lucide-react';
import Link from 'next/link';

export default function ProfitCalculator() {
  const [miles, setMiles] = useState(2500);
  const [rate, setRate] = useState(2.5);
  const [deadhead, setDeadhead] = useState(15);

  const results = useMemo(() => {
    const milesDriven = Number(miles);
    const ratePerMile = Number(rate);
    const deadheadPercent = Number(deadhead) / 100;
    
    if (!milesDriven || !ratePerMile) {
        return { currentRevenue: 0, potentialRevenue: 0, weeklyIncrease: 0, annualIncrease: 0 };
    }

    // Assumed improvements with H&A Dispatch
    const deadheadReductionFactor = 0.5; // We cut deadhead in half
    const rateIncreaseFactor = 1.15; // We increase rates by 15%

    const currentLoadedMiles = milesDriven * (1 - deadheadPercent);
    const currentRevenue = currentLoadedMiles * ratePerMile;

    const newDeadheadPercent = deadheadPercent * deadheadReductionFactor;
    const newRatePerMile = ratePerMile * rateIncreaseFactor;
    const newLoadedMiles = milesDriven * (1 - newDeadheadPercent);
    const potentialRevenue = newLoadedMiles * newRatePerMile;

    const weeklyIncrease = potentialRevenue - currentRevenue;
    const annualIncrease = weeklyIncrease * 52;
    
    return {
      currentRevenue,
      potentialRevenue,
      weeklyIncrease,
      annualIncrease,
    };
  }, [miles, rate, deadhead]);

  const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    }).format(value);
  };

  return (
    <section id="calculator" className="w-full py-16 md:py-24">
      <div className="container mx-auto px-4 md:px-6">
        <div className="mx-auto max-w-3xl text-center mb-12">
           <div className="inline-flex items-center gap-2 bg-primary/10 text-primary font-medium px-4 py-1 rounded-full mb-4">
            <Calculator className="h-5 w-5" />
            <span>Profit Calculator</span>
          </div>
          <h2 className="text-3xl md:text-4xl font-bold tracking-tighter">
            See Your Potential Earnings
          </h2>
          <p className="mt-4 text-lg text-muted-foreground">
            Use our calculator to estimate how much more you could earn with H&A Dispatch optimizing your loads and routes.
          </p>
        </div>

        <Card className="max-w-5xl mx-auto shadow-lg">
          <div className="grid md:grid-cols-2">
            <div className="p-8 space-y-6">
               <CardHeader className="p-0">
                <CardTitle>Your Weekly Average</CardTitle>
              </CardHeader>
              <div className="space-y-4">
                <Label htmlFor="miles" className="text-base">Total Miles Driven per Week</Label>
                <Input id="miles" type="number" value={miles} onChange={(e) => setMiles(Number(e.target.value))} className="text-lg" />
                 <Slider value={[miles]} onValueChange={([val]) => setMiles(val)} max={5000} step={100} />
              </div>
               <div className="space-y-4">
                <Label htmlFor="rate" className="text-base">Average Rate per Mile ($)</Label>
                <Input id="rate" type="number" value={rate} onChange={(e) => setRate(Number(e.target.value))} step="0.01" className="text-lg" />
                 <Slider value={[rate]} onValueChange={([val]) => setRate(val)} max={5} step={0.10} />
              </div>
               <div className="space-y-4">
                <Label htmlFor="deadhead" className="text-base">Deadhead Percentage (%)</Label>
                <Input id="deadhead" type="number" value={deadhead} onChange={(e) => setDeadhead(Number(e.target.value))} className="text-lg" />
                <Slider value={[deadhead]} onValueChange={([val]) => setDeadhead(val)} max={50} step={1} />
              </div>
            </div>
            <div className="p-8 bg-secondary/30 flex flex-col justify-center rounded-r-lg">
                <CardHeader className="p-0 mb-6">
                    <div className="flex items-center gap-3 mb-2">
                        <TrendingUp className="h-8 w-8 text-primary"/>
                        <CardTitle className="text-2xl">Your Potential Growth</CardTitle>
                    </div>
                    <CardDescription>Estimated increase with H&A Dispatch handling your logistics.</CardDescription>
                </CardHeader>
                <CardContent className="p-0 space-y-6">
                    <div className="text-center bg-background/50 p-6 rounded-lg shadow-inner">
                        <p className="text-sm text-muted-foreground font-medium uppercase tracking-wider">Potential Annual Profit Increase</p>
                        <p className="text-4xl md:text-5xl font-bold text-primary tracking-tight">{formatCurrency(results.annualIncrease)}</p>
                    </div>
                     <div className="text-center">
                        <p className="text-muted-foreground">That's an extra <span className="font-bold text-foreground">{formatCurrency(results.weeklyIncrease)}</span> per week!</p>
                    </div>
                    <Button asChild size="lg" className="w-full font-semibold">
                      <Link href="#book">
                        Start Your Free Trial <ArrowRight className="ml-2 h-5 w-5" />
                      </Link>
                    </Button>
                </CardContent>
            </div>
          </div>
        </Card>
      </div>
    </section>
  );
}
