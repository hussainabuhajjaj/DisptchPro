
'use client';

import { useState } from 'react';
import { useForm, FormProvider, useFormContext, useFieldArray, Control } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import * as z from 'zod';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage, FormDescription } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { Separator } from '@/components/ui/separator';
import { cn } from '@/lib/utils';
import { ArrowRight, ArrowLeft, LoaderCircle, Check, PlusCircle, Trash2 } from 'lucide-react';
import { Textarea } from '../ui/textarea';
import { Checkbox } from '../ui/checkbox';
import { RadioGroup, RadioGroupItem } from '../ui/radio-group';

const carrierInfoSchema = z.object({
  companyName: z.string().min(1, 'Company name is required'),
  dba: z.string().optional(),
  physicalAddress: z.string().min(1, 'Physical address is required'),
  physicalCity: z.string().min(1, 'City is required'),
  physicalState: z.string().min(1, 'State is required'),
  physicalZip: z.string().min(1, 'ZIP code is required'),
  mailingAddress: z.string().optional(),
  mailingCity: z.string().optional(),
  mailingState: z.string().optional(),
  mailingZip: z.string().optional(),
  mainContact: z.string().min(1, 'Main contact is required'),
  email: z.string().email('Invalid email address'),
  officePhone: z.string().min(1, 'Office phone is required'),
  fax: z.string().optional(),
  cellPhone: z.string().min(1, 'Cell phone is required'),
  emergencyContact: z.string().min(1, 'Emergency contact is required'),
  emergencyPhone: z.string().min(1, 'Emergency phone is required'),
  mcNumber: z.string().min(1, 'MC # is required'),
  dotNumber: z.string().min(1, 'DOT # is required'),
  einNumber: z.string().min(1, 'EIN # is required'),
  scacCode: z.string().optional(),
  twicCertified: z.string().optional(),
  hazmatCertified: z.string().optional(),
});

const equipmentInfoSchema = z.object({
  numTrucks: z.string().optional(),
  companyDrivers: z.string().optional(),
  ownerOperators: z.string().optional(),
  teamDrivers: z.string().optional(),
  numTrailers: z.string().optional(),
  vanTrailers: z.string().optional(),
  reeferTrailers: z.string().optional(),
  flatbedTrailers: z.string().optional(),
  tankerTrailers: z.string().optional(),
  otherTrailerTypes: z.string().optional(),
  vanSizes: z.string().optional(),
  reeferSizes: z.string().optional(),
  flatbedSizes: z.string().optional(),
  tankerSizes: z.string().optional(),
  tractors: z.array(z.object({
    year: z.string(),
    makeModel: z.string(),
    truckNum: z.string(),
    vin: z.string(),
  })).optional(),
  trailers: z.array(z.object({
    year: z.string(),
    makeModel: z.string(),
    trailerNum: z.string(),
    vin: z.string(),
  })).optional(),
  drivers: z.array(z.object({
    truckNum: z.string(),
    trailerNum: z.string(),
    trailerType: z.string(),
    maxWeight: z.string(),
    driverName: z.string(),
    driverCell: z.string(),
  })).optional(),
  driversCanMakeDecisions: z.enum(["Yes", "No"]).optional(),
  driversNeedCopy: z.enum(["Yes", "No"]).optional(),
  equipmentDescription: z.string().optional(),
});

const operationInfoSchema = z.object({
    canadaProvinces: z.string().optional(),
    mexico: z.string().optional(),
    states: z.array(z.string()).optional(),
    minRatePerMile: z.string().optional(),
    maxPicks: z.string().optional(),
    maxDrops: z.string().optional(),
    perPickDrop: z.string().optional(),
    driverTouch: z.enum(["Yes", "No"]).optional(),
    driverTouchComments: z.string().optional(),
});

const factoringInfoSchema = z.object({
    factoringCompany: z.string().optional(),
    mainContact: z.string().optional(),
    phone: z.string().optional(),
    fax: z.string().optional(),
    websiteUrl: z.string().optional(),
    address: z.string().optional(),
    city: z.string().optional(),
    state: z.string().optional(),
    zip: z.string().optional(),
});

const insuranceInfoSchema = z.object({
    insuranceAgency: z.string().optional(),
    mainContact: z.string().optional(),
    phone: z.string().optional(),
    fax: z.string().optional(),
    email: z.string().email().optional().or(z.literal('')),
    address: z.string().optional(),
    city: z.string().optional(),
    state: z.string().optional(),
    zip: z.string().optional(),
    companyDescription: z.string().optional(),
});


const fullFormSchema = z.object({
  carrierInfo: carrierInfoSchema,
  equipmentInfo: equipmentInfoSchema,
  operationInfo: operationInfoSchema,
  factoringInfo: factoringInfoSchema,
  insuranceInfo: insuranceInfoSchema,
});

type FullFormValues = z.infer<typeof fullFormSchema>;

const steps = [
  { id: 'carrier-info', title: 'Carrier Information', schema: carrierInfoSchema },
  { id: 'equipment', title: 'Equipment', schema: equipmentInfoSchema },
  { id: 'operation', title: 'Operation', schema: operationInfoSchema },
  { id: 'factoring', title: 'Factoring', schema: factoringInfoSchema },
  { id: 'insurance', title: 'Insurance & Details', schema: insuranceInfoSchema },
];

function CarrierInfoForm() {
    return (
        <div className="space-y-6">
            <h3 className="text-lg font-medium">Company Details</h3>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <FormField name="carrierInfo.companyName" render={({ field }) => (
                    <FormItem>
                        <FormLabel>Company Name</FormLabel>
                        <FormControl><Input {...field} /></FormControl>
                        <FormMessage />
                    </FormItem>
                )} />
                <FormField name="carrierInfo.dba" render={({ field }) => (
                    <FormItem>
                        <FormLabel>DBA (If any)</FormLabel>
                        <FormControl><Input {...field} /></FormControl>
                        <FormMessage />
                    </FormItem>
                )} />
            </div>

            <Separator />
            <h3 className="text-lg font-medium">Physical Address</h3>
             <div className="space-y-4">
                <FormField name="carrierInfo.physicalAddress" render={({ field }) => (
                    <FormItem><FormLabel>Address</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <FormField name="carrierInfo.physicalCity" render={({ field }) => (
                        <FormItem><FormLabel>City</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                    )} />
                    <FormField name="carrierInfo.physicalState" render={({ field }) => (
                        <FormItem><FormLabel>State</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                    )} />
                    <FormField name="carrierInfo.physicalZip" render={({ field }) => (
                        <FormItem><FormLabel>Zip Code</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                    )} />
                </div>
            </div>

            <Separator />
            <h3 className="text-lg font-medium">Mailing Address (if different)</h3>
             <div className="space-y-4">
                <FormField name="carrierInfo.mailingAddress" render={({ field }) => (
                    <FormItem><FormLabel>Address</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <FormField name="carrierInfo.mailingCity" render={({ field }) => (
                        <FormItem><FormLabel>City</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                    )} />
                    <FormField name="carrierInfo.mailingState" render={({ field }) => (
                        <FormItem><FormLabel>State</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                    )} />
                    <FormField name="carrierInfo.mailingZip" render={({ field }) => (
                        <FormItem><FormLabel>Zip Code</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                    )} />
                </div>
            </div>
            
            <Separator />
            <h3 className="text-lg font-medium">Contact Information</h3>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                 <FormField name="carrierInfo.mainContact" render={({ field }) => (
                    <FormItem><FormLabel>Main Contact</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                 <FormField name="carrierInfo.email" render={({ field }) => (
                    <FormItem><FormLabel>Email</FormLabel><FormControl><Input type="email" {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                 <FormField name="carrierInfo.officePhone" render={({ field }) => (
                    <FormItem><FormLabel>Office Phone</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                 <FormField name="carrierInfo.fax" render={({ field }) => (
                    <FormItem><FormLabel>Fax</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                 <FormField name="carrierInfo.cellPhone" render={({ field }) => (
                    <FormItem><FormLabel>Cell Phone</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                )} />
            </div>
             <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                 <FormField name="carrierInfo.emergencyContact" render={({ field }) => (
                    <FormItem><FormLabel>Emergency Contact</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                 <FormField name="carrierInfo.emergencyPhone" render={({ field }) => (
                    <FormItem><FormLabel>Emergency Phone</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                )} />
            </div>

            <Separator />
            <h3 className="text-lg font-medium">Authority & Certification</h3>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                 <FormField name="carrierInfo.mcNumber" render={({ field }) => (
                    <FormItem><FormLabel>MC #</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                 <FormField name="carrierInfo.dotNumber" render={({ field }) => (
                    <FormItem><FormLabel>DOT #</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                 <FormField name="carrierInfo.einNumber" render={({ field }) => (
                    <FormItem><FormLabel>EIN #</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                 <FormField name="carrierInfo.scacCode" render={({ field }) => (
                    <FormItem><FormLabel>SCAC Code</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                 <FormField name="carrierInfo.twicCertified" render={({ field }) => (
                    <FormItem><FormLabel>TWIC Certified</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                 <FormField name="carrierInfo.hazmatCertified" render={({ field }) => (
                    <FormItem><FormLabel>Hazmat Certified</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                )} />
            </div>
        </div>
    );
}

function DynamicTable<T extends string>({ name, columns, control }: { name: T, columns: { key: string, label: string }[], control: Control<any> }) {
    const { fields, append, remove } = useFieldArray({ control, name });

    return (
        <div className="space-y-4">
            <div className="overflow-x-auto">
                <table className="w-full text-sm">
                    <thead>
                        <tr className="border-b">
                            {columns.map(col => <th key={col.key} className="p-2 text-left font-medium">{col.label}</th>)}
                            <th className="w-12 p-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        {fields.map((field, index) => (
                            <tr key={field.id} className="border-b">
                                {columns.map(col => (
                                    <td key={col.key} className="p-1">
                                        <FormField
                                            control={control}
                                            name={`${name}.${index}.${col.key}`}
                                            render={({ field }) => <Input {...field} className="h-8" />}
                                        />
                                    </td>
                                ))}
                                <td className="p-1">
                                    <Button type="button" variant="ghost" size="icon" className="h-8 w-8" onClick={() => remove(index)}>
                                        <Trash2 className="h-4 w-4 text-destructive" />
                                    </Button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
            <Button
                type="button"
                variant="outline"
                size="sm"
                onClick={() => append(columns.reduce((acc, col) => ({ ...acc, [col.key]: '' }), {}))}
            >
                <PlusCircle className="mr-2 h-4 w-4" />
                Add Row
            </Button>
        </div>
    );
}

function EquipmentInfoForm() {
    const { control } = useFormContext();

    return (
        <div className="space-y-6">
            <h3 className="text-lg font-medium">Fleet Composition</h3>
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                <FormField name="equipmentInfo.numTrucks" render={({ field }) => (
                    <FormItem><FormLabel># of Trucks</FormLabel><FormControl><Input type="number" {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                <FormField name="equipmentInfo.companyDrivers" render={({ field }) => (
                    <FormItem><FormLabel>Company Drivers</FormLabel><FormControl><Input type="number" {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                <FormField name="equipmentInfo.ownerOperators" render={({ field }) => (
                    <FormItem><FormLabel>Owner Operators</FormLabel><FormControl><Input type="number" {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                <FormField name="equipmentInfo.teamDrivers" render={({ field }) => (
                    <FormItem><FormLabel>Team Drivers</FormLabel><FormControl><Input type="number" {...field} /></FormControl><FormMessage /></FormItem>
                )} />
            </div>

            <Separator />
            <h3 className="text-lg font-medium">Trailer Types</h3>
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                 <FormField name="equipmentInfo.numTrailers" render={({ field }) => (
                    <FormItem><FormLabel># of Trailers</FormLabel><FormControl><Input type="number" {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                <FormField name="equipmentInfo.vanTrailers" render={({ field }) => (
                    <FormItem><FormLabel>Van</FormLabel><FormControl><Input type="number" {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                <FormField name="equipmentInfo.reeferTrailers" render={({ field }) => (
                    <FormItem><FormLabel>Reefers</FormLabel><FormControl><Input type="number" {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                <FormField name="equipmentInfo.flatbedTrailers" render={({ field }) => (
                    <FormItem><FormLabel>Flatbed</FormLabel><FormControl><Input type="number" {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                 <FormField name="equipmentInfo.tankerTrailers" render={({ field }) => (
                    <FormItem><FormLabel>Tanker</FormLabel><FormControl><Input type="number" {...field} /></FormControl><FormMessage /></FormItem>
                )} />
            </div>
            <FormField name="equipmentInfo.otherTrailerTypes" render={({ field }) => (
                <FormItem><FormLabel>Other Trailer Types</FormLabel><FormControl><Textarea {...field} /></FormControl><FormMessage /></FormItem>
            )} />

             <Separator />
            <h3 className="text-lg font-medium">Trailer Sizes</h3>
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                <FormField name="equipmentInfo.vanSizes" render={({ field }) => (
                    <FormItem><FormLabel>Van</FormLabel><FormControl><Input {...field} placeholder="e.g., 53', 48'" /></FormControl><FormMessage /></FormItem>
                )} />
                <FormField name="equipmentInfo.reeferSizes" render={({ field }) => (
                    <FormItem><FormLabel>Reefers</FormLabel><FormControl><Input {...field} placeholder="e.g., 53', 48'" /></FormControl><FormMessage /></FormItem>
                )} />
                <FormField name="equipmentInfo.flatbedSizes" render={({ field }) => (
                    <FormItem><FormLabel>Flatbed</FormLabel><FormControl><Input {...field} placeholder="e.g., 48', Step-deck" /></FormControl><FormMessage /></FormItem>
                )} />
                <FormField name="equipmentInfo.tankerSizes" render={({ field }) => (
                    <FormItem><FormLabel>Tanker</FormLabel><FormControl><Input {...field} placeholder="e.g., Food grade, Hazmat" /></FormControl><FormMessage /></FormItem>
                )} />
            </div>
            
            <Separator />
            <h3 className="text-lg font-medium">Tractor Information</h3>
            <DynamicTable name="equipmentInfo.tractors" control={control} columns={[
                { key: 'year', label: 'Year' },
                { key: 'makeModel', label: 'Make/Model' },
                { key: 'truckNum', label: 'Truck #' },
                { key: 'vin', label: 'VIN #' },
            ]} />

            <Separator />
            <h3 className="text-lg font-medium">Trailer Information</h3>
            <DynamicTable name="equipmentInfo.trailers" control={control} columns={[
                { key: 'year', label: 'Year' },
                { key: 'makeModel', label: 'Make/Model' },
                { key: 'trailerNum', label: 'Trailer #' },
                { key: 'vin', label: 'VIN #' },
            ]} />

            <Separator />
            <h3 className="text-lg font-medium">Driver Information</h3>
            <DynamicTable name="equipmentInfo.drivers" control={control} columns={[
                { key: 'truckNum', label: 'Truck #' },
                { key: 'trailerNum', label: 'Trailer #' },
                { key: 'trailerType', label: 'Trailer Type' },
                { key: 'maxWeight', label: 'Max Weight' },
                { key: 'driverName', label: 'Driver Name' },
                { key: 'driverCell', label: 'Driver Cell' },
            ]} />

            <Separator />
            <div className="space-y-4">
                 <FormField
                  control={control}
                  name="equipmentInfo.driversCanMakeDecisions"
                  render={({ field }) => (
                    <FormItem className="space-y-2">
                      <FormLabel>Do the assigned drivers have the right to make load decisions for you?</FormLabel>
                      <FormControl>
                        <RadioGroup onValueChange={field.onChange} defaultValue={field.value} className="flex items-center space-x-4">
                          <FormItem className="flex items-center space-x-2 space-y-0"><FormControl><RadioGroupItem value="Yes" /></FormControl><FormLabel className="font-normal">Yes</FormLabel></FormItem>
                          <FormItem className="flex items-center space-x-2 space-y-0"><FormControl><RadioGroupItem value="No" /></FormControl><FormLabel className="font-normal">No</FormLabel></FormItem>
                        </RadioGroup>
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />
                 <FormField
                  control={control}
                  name="equipmentInfo.driversNeedCopy"
                  render={({ field }) => (
                    <FormItem className="space-y-2">
                      <FormLabel>Do the assigned drivers need to have a copy of the load confirmation?</FormLabel>
                       <FormControl>
                        <RadioGroup onValueChange={field.onChange} defaultValue={field.value} className="flex items-center space-x-4">
                            <FormItem className="flex items-center space-x-2 space-y-0"><FormControl><RadioGroupItem value="Yes" /></FormControl><FormLabel className="font-normal">Yes</FormLabel></FormItem>
                            <FormItem className="flex items-center space-x-2 space-y-0"><FormControl><RadioGroupItem value="No" /></FormControl><FormLabel className="font-normal">No</FormLabel></FormItem>
                        </RadioGroup>
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />
                <FormField name="equipmentInfo.equipmentDescription" render={({ field }) => (
                    <FormItem>
                        <FormLabel>Provide a detailed description of the equipment (i.e. pallets, tarps, oversize, and weight limits):</FormLabel>
                        <FormControl><Textarea {...field} /></FormControl>
                        <FormMessage />
                    </FormItem>
                )} />
            </div>
        </div>
    );
}

const usStates = ["AL", "AR", "AZ", "CA", "CO", "CT", "DE", "FL", "GA", "IA", "ID", "IL", "IN", "KS", "KY", "LA", "MA", "MD", "ME", "MI", "MO", "MN", "MS", "MT", "NC", "ND", "NE", "NH", "NJ", "NM", "NV", "NY", "OH", "OK", "OR", "PA", "RI", "SC", "SD", "TN", "TX", "UT", "VA", "VT", "WA", "WI", "WV", "WY"];

function OperationInfoForm() {
    const { control } = useFormContext();

    return (
        <div className="space-y-6">
            <h3 className="text-lg font-medium">International Operation</h3>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <FormField name="operationInfo.canadaProvinces" render={({ field }) => (
                    <FormItem><FormLabel>Canada (list provinces)</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                <FormField name="operationInfo.mexico" render={({ field }) => (
                    <FormItem><FormLabel>Mexico</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                )} />
            </div>

            <Separator />
            <FormField
                control={control}
                name="operationInfo.states"
                render={() => (
                    <FormItem>
                        <div className="mb-4">
                            <FormLabel className="text-lg font-medium">US States of Operation</FormLabel>
                            <FormDescription>Select all states that apply.</FormDescription>
                        </div>
                        <div className="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-4">
                            {usStates.map((state) => (
                                <FormField
                                    key={state}
                                    control={control}
                                    name="operationInfo.states"
                                    render={({ field }) => (
                                        <FormItem key={state} className="flex flex-row items-center space-x-2 space-y-0">
                                            <FormControl>
                                                <Checkbox
                                                    checked={field.value?.includes(state)}
                                                    onCheckedChange={(checked) => {
                                                        return checked
                                                            ? field.onChange([...(field.value || []), state])
                                                            : field.onChange(field.value?.filter((value: string) => value !== state));
                                                    }}
                                                />
                                            </FormControl>
                                            <FormLabel className="font-normal">{state}</FormLabel>
                                        </FormItem>
                                    )}
                                />
                            ))}
                        </div>
                        <FormMessage />
                    </FormItem>
                )}
            />

            <Separator />
            <h3 className="text-lg font-medium">Rate of Haul</h3>
            <p className="text-sm text-muted-foreground">Please give us your minimum rate information. We understand that many factors will change this, but this will give us a starting point.</p>
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                <FormField name="operationInfo.minRatePerMile" render={({ field }) => (
                    <FormItem><FormLabel>Min. Rate / Mile</FormLabel><FormControl><Input type="number" {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                <FormField name="operationInfo.maxPicks" render={({ field }) => (
                    <FormItem><FormLabel>Max Picks</FormLabel><FormControl><Input type="number" {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                <FormField name="operationInfo.maxDrops" render={({ field }) => (
                    <FormItem><FormLabel>Max Drops</FormLabel><FormControl><Input type="number" {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                <FormField name="operationInfo.perPickDrop" render={({ field }) => (
                    <FormItem><FormLabel>$ Per Pick/Drop</FormLabel><FormControl><Input type="number" {...field} /></FormControl><FormMessage /></FormItem>
                )} />
            </div>

            <Separator />
             <div className="space-y-4">
                <FormField
                  control={control}
                  name="operationInfo.driverTouch"
                  render={({ field }) => (
                    <FormItem className="space-y-3">
                      <FormLabel>Driver Touch (Y/N)</FormLabel>
                      <FormControl>
                        <RadioGroup
                          onValueChange={field.onChange}
                          defaultValue={field.value}
                          className="flex items-center space-x-4"
                        >
                          <FormItem className="flex items-center space-x-2 space-y-0">
                            <FormControl>
                              <RadioGroupItem value="Yes" />
                            </FormControl>
                            <FormLabel className="font-normal">Yes</FormLabel>
                          </FormItem>
                          <FormItem className="flex items-center space-x-2 space-y-0">
                            <FormControl>
                              <RadioGroupItem value="No" />
                            </FormControl>
                            <FormLabel className="font-normal">No</FormLabel>
                          </FormItem>
                        </RadioGroup>
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />
                 <FormField name="operationInfo.driverTouchComments" render={({ field }) => (
                    <FormItem>
                        <FormLabel>Comments</FormLabel>
                        <FormControl><Textarea {...field} /></FormControl>
                        <FormMessage />
                    </FormItem>
                )} />
             </div>
        </div>
    );
}

function FactoringInfoForm() {
    return (
        <div className="space-y-6">
            <h3 className="text-lg font-medium">Factoring Information</h3>
            <p className="text-sm text-muted-foreground">If you use a factoring service, please provide us with the following information. This will ensure that we only use brokers that are approved by your factoring company. If you don’t use a factoring service but would like to, please let us know so we can source one for you.</p>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <FormField name="factoringInfo.factoringCompany" render={({ field }) => (
                    <FormItem><FormLabel>Factoring Company</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                <FormField name="factoringInfo.mainContact" render={({ field }) => (
                    <FormItem><FormLabel>Main Contact</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                <FormField name="factoringInfo.phone" render={({ field }) => (
                    <FormItem><FormLabel>Phone</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                <FormField name="factoringInfo.fax" render={({ field }) => (
                    <FormItem><FormLabel>Fax</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                 <FormField name="factoringInfo.websiteUrl" render={({ field }) => (
                    <FormItem className="md:col-span-2"><FormLabel>Website URL</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                )} />
            </div>
            
            <Separator />
            <h3 className="text-lg font-medium">Factoring Company Address</h3>
            <div className="space-y-4">
                 <FormField name="factoringInfo.address" render={({ field }) => (
                    <FormItem><FormLabel>Address</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <FormField name="factoringInfo.city" render={({ field }) => (
                        <FormItem><FormLabel>City</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                    )} />
                    <FormField name="factoringInfo.state" render={({ field }) => (
                        <FormItem><FormLabel>State</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                    )} />
                    <FormField name="factoringInfo.zip" render={({ field }) => (
                        <FormItem><FormLabel>Zip Code</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                    )} />
                </div>
            </div>
        </div>
    );
}

function InsuranceInfoForm() {
    return (
        <div className="space-y-6">
            <h3 className="text-lg font-medium">Insurance Information</h3>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <FormField name="insuranceInfo.insuranceAgency" render={({ field }) => (
                    <FormItem><FormLabel>Insurance Agency</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                <FormField name="insuranceInfo.mainContact" render={({ field }) => (
                    <FormItem><FormLabel>Main Contact</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                <FormField name="insuranceInfo.phone" render={({ field }) => (
                    <FormItem><FormLabel>Phone</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                <FormField name="insuranceInfo.fax" render={({ field }) => (
                    <FormItem><FormLabel>Fax</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                <FormField name="insuranceInfo.email" render={({ field }) => (
                    <FormItem className="md:col-span-2"><FormLabel>Email</FormLabel><FormControl><Input type="email" {...field} /></FormControl><FormMessage /></FormItem>
                )} />
            </div>

            <Separator />
            <h3 className="text-lg font-medium">Insurance Agency Address</h3>
            <div className="space-y-4">
                <FormField name="insuranceInfo.address" render={({ field }) => (
                    <FormItem><FormLabel>Address</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <FormField name="insuranceInfo.city" render={({ field }) => (
                        <FormItem><FormLabel>City</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                    )} />
                    <FormField name="insuranceInfo.state" render={({ field }) => (
                        <FormItem><FormLabel>State</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                    )} />
                    <FormField name="insuranceInfo.zip" render={({ field }) => (
                        <FormItem><FormLabel>Zip Code</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                    )} />
                </div>
            </div>

            <Separator />
             <FormField name="insuranceInfo.companyDescription" render={({ field }) => (
                <FormItem>
                    <FormLabel>Provide any additional details to better describe your company:</FormLabel>
                    <FormControl><Textarea {...field} rows={5} /></FormControl>
                    <FormMessage />
                </FormItem>
            )} />

        </div>
    );
}


export default function CarrierProfileForm() {
  const [currentStep, setCurrentStep] = useState(0);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [formCompleted, setFormCompleted] = useState(false);

  const methods = useForm<FullFormValues>({
    resolver: zodResolver(fullFormSchema),
    defaultValues: {
      carrierInfo: {
        companyName: '', dba: '', physicalAddress: '', physicalCity: '',
        physicalState: '', physicalZip: '', mailingAddress: '', mailingCity: '',
        mailingState: '', mailingZip: '', mainContact: '', email: '',
        officePhone: '', fax: '', cellPhone: '', emergencyContact: '',
        emergencyPhone: '', mcNumber: '', dotNumber: '', einNumber: '',
        scacCode: '', twicCertified: '', hazmatCertified: '',
      },
      equipmentInfo: {
        numTrucks: '', companyDrivers: '', ownerOperators: '', teamDrivers: '',
        numTrailers: '', vanTrailers: '', reeferTrailers: '', flatbedTrailers: '',
        tankerTrailers: '', otherTrailerTypes: '', vanSizes: '', reeferSizes: '',
        flatbedSizes: '', tankerSizes: '', tractors: [], trailers: [], drivers: [],
        equipmentDescription: '',
      },
      operationInfo: {
        states: [], canadaProvinces: '', mexico: '', minRatePerMile: '',
        maxPicks: '', maxDrops: '', perPickDrop: '', driverTouchComments: '',
      },
      factoringInfo: {
        factoringCompany: '', mainContact: '', phone: '', fax: '',
        websiteUrl: '', address: '', city: '', state: '', zip: '',
      },
      insuranceInfo: {
        insuranceAgency: '', mainContact: '', phone: '', fax: '', email: '',
        address: '', city: '', state: '', zip: '', companyDescription: '',
      }
    },
    mode: 'onChange',
  });

  const { handleSubmit, trigger, getValues } = methods;

  const processForm = async (data: FullFormValues) => {
    setIsSubmitting(true);
    console.log('Form data:', data);
    // Here you would typically save the data to Firestore
    await new Promise(resolve => setTimeout(resolve, 2000));
    setIsSubmitting(false);
    setFormCompleted(true);
  };
  
  const nextStep = async () => {
    const stepSchema = steps[currentStep].schema;
    const stepId = steps[currentStep].id as keyof FullFormValues;
    const stepValues = getValues(stepId);
    
    // We use safeParse to avoid throwing an error and stopping the form.
    // The errors will be displayed on the fields instead.
    const result = await stepSchema.safeParseAsync(stepValues);
    if (!result.success) {
        // Manually trigger validation for all fields in the current step to show errors
        const fields = Object.keys(stepSchema.shape).map(key => `${stepId}.${key}`);
        await trigger(fields as any, { shouldFocus: true });
        return;
    }
    
    if (currentStep < steps.length - 1) {
      setCurrentStep(step => step + 1);
    }
  };

  const prevStep = () => {
    if (currentStep > 0) {
      setCurrentStep(step => step - 1);
    }
  };

  if (isSubmitting) {
    return (
        <div className="flex flex-col items-center justify-center text-center gap-4 py-24">
            <LoaderCircle className="h-16 w-16 animate-spin text-primary" />
            <h2 className="text-2xl font-bold tracking-tighter">Submitting Your Profile</h2>
            <p className="text-lg text-muted-foreground">Please wait a moment...</p>
        </div>
    );
  }

  if (formCompleted) {
    return (
        <div className="flex flex-col items-center justify-center text-center gap-4 py-24 px-4">
            <Check className="h-16 w-16 text-green-500" />
            <h2 className="text-2xl font-bold tracking-tighter">Profile Submitted!</h2>
            <p className="text-lg text-muted-foreground max-w-2xl">
              Thank you for completing your profile. We will review it and get in touch shortly.
              <br/>
              <span className="font-semibold mt-2 block">Please keep a blank copy of this form, and email updates to us when they occur, this way we have the most current information on hand.</span>
            </p>
        </div>
    );
  }

  return (
    <Card className="w-full">
        <CardHeader>
            <div className="flex items-start justify-center p-4">
              <ol className="flex items-center w-full max-w-2xl">
                {steps.map((step, index) => (
                  <li key={step.id} className={cn(
                      "relative flex w-full items-center",
                      index < steps.length - 1 ? "after:content-[''] after:w-full after:h-1 after:border-b after:border-4 after:inline-block" : "",
                      index <= currentStep ? "after:border-primary" : "after:border-muted",
                  )}>
                    <div className="flex flex-col items-center">
                        <div className={cn(
                            "flex items-center justify-center w-10 h-10 rounded-full shrink-0",
                            index <= currentStep ? "bg-primary text-primary-foreground" : "bg-muted text-muted-foreground"
                        )}>
                            {index < currentStep ? <Check className="w-6 h-6"/> : <span className="font-bold text-lg">{index + 1}</span>}
                        </div>
                        <p className={cn(
                            "text-xs text-center mt-2 w-20 md:w-auto",
                             index <= currentStep ? "font-bold text-primary" : "text-muted-foreground",
                             "hidden md:block"
                        )}>{step.title}</p>
                    </div>
                  </li>
                ))}
              </ol>
            </div>
            <Separator />
        </CardHeader>
      <CardContent className="p-4 md:p-6">
        <FormProvider {...methods}>
          <form onSubmit={handleSubmit(processForm)} className="space-y-8">
            {currentStep === 0 && <CarrierInfoForm />}
            {currentStep === 1 && <EquipmentInfoForm />}
            {currentStep === 2 && <OperationInfoForm />}
            {currentStep === 3 && <FactoringInfoForm />}
            {currentStep === 4 && <InsuranceInfoForm />}
          </form>
        </FormProvider>
      </CardContent>
      <div className="p-6 flex justify-between border-t">
        <Button type="button" variant="outline" onClick={prevStep} disabled={currentStep === 0}>
           <ArrowLeft className="mr-2 h-4 w-4"/> Previous
        </Button>
        {currentStep < steps.length - 1 ? (
          <Button type="button" onClick={nextStep}>
            Next <ArrowRight className="ml-2 h-4 w-4"/>
          </Button>
        ) : (
          <Button type="submit" onClick={handleSubmit(processForm)}>
            Submit Profile
          </Button>
        )}
      </div>
    </Card>
  );
}

    