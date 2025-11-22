

'use client';

import { useState } from 'react';
import { useForm, FormProvider, useFormContext, useFieldArray, Control, FieldPath, useWatch } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import * as z from 'zod';
import { useAuth, useFirestore } from '@/firebase';
import { doc, writeBatch } from 'firebase/firestore';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage, FormDescription } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { Separator } from '@/components/ui/separator';
import { cn } from '@/lib/utils';
import { ArrowRight, ArrowLeft, LoaderCircle, Check, PlusCircle, Trash2, Edit } from 'lucide-react';
import { Textarea } from '../ui/textarea';
import { Checkbox } from '../ui/checkbox';
import { RadioGroup, RadioGroupItem } from '../ui/radio-group';
import { ScrollArea } from '../ui/scroll-area';

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

export type FullFormValues = z.infer<typeof fullFormSchema>;

const steps = [
  { id: 'carrierInfo' as const, title: 'Carrier Information', schema: carrierInfoSchema },
  { id: 'equipmentInfo' as const, title: 'Equipment', schema: equipmentInfoSchema },
  { id: 'operationInfo' as const, title: 'Operation', schema: operationInfoSchema },
  { id: 'factoringInfo' as const, title: 'Factoring', schema: factoringInfoSchema },
  { id: 'insuranceInfo' as const, title: 'Insurance & Details', schema: insuranceInfoSchema },
  { id: 'preview' as const, title: 'Preview & Submit' },
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

const PreviewSection = ({ title, onEdit, children }: { title: string; onEdit: () => void; children: React.ReactNode }) => (
    <div className="space-y-4">
        <div className="flex items-center justify-between">
            <h3 className="text-lg font-medium text-primary">{title}</h3>
            <Button variant="ghost" size="sm" onClick={onEdit}>
                <Edit className="mr-2 h-4 w-4" /> Edit
            </Button>
        </div>
        <div className="space-y-2 rounded-md border p-4 text-sm">{children}</div>
    </div>
);

const PreviewItem = ({ label, value }: { label: string, value?: string | number | string[] | null }) => {
  if (!value || (Array.isArray(value) && value.length === 0)) return null;
  return (
    <div className="flex flex-col sm:flex-row sm:gap-2">
      <p className="font-semibold w-full sm:w-1/3">{label}:</p>
      <p className="w-full sm:w-2/3">{Array.isArray(value) ? value.join(', ') : value}</p>
    </div>
  );
};

const PreviewTable = ({ title, data, columns }: { title: string, data?: any[], columns: { key: string, label: string }[] }) => {
  if (!data || data.length === 0) return null;
  return (
    <div>
        <h4 className="font-semibold mt-4 mb-2">{title}</h4>
        <div className="overflow-x-auto rounded-md border">
            <table className="w-full text-left text-sm">
                <thead className="bg-muted/50">
                    <tr>{columns.map(c => <th key={c.key} className="p-2 font-medium">{c.label}</th>)}</tr>
                </thead>
                <tbody>
                    {data.map((row, i) => (
                        <tr key={i} className="border-t">
                            {columns.map(c => <td key={c.key} className="p-2">{row[c.key]}</td>)}
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    </div>
  );
}


function PreviewForm({ onEdit }: { onEdit: (step: number) => void }) {
    const { control } = useFormContext<FullFormValues>();
    const formData = useWatch({ control });
    const { carrierInfo, equipmentInfo, operationInfo, factoringInfo, insuranceInfo } = formData;

    return (
        <ScrollArea className="h-[60vh]">
        <div className="space-y-8 p-1">
            <PreviewSection title="Carrier Information" onEdit={() => onEdit(0)}>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2">
                    <PreviewItem label="Company Name" value={carrierInfo.companyName} />
                    <PreviewItem label="DBA" value={carrierInfo.dba} />
                    <PreviewItem label="Physical Address" value={`${carrierInfo.physicalAddress}, ${carrierInfo.physicalCity}, ${carrierInfo.physicalState} ${carrierInfo.physicalZip}`} />
                    {carrierInfo.mailingAddress && <PreviewItem label="Mailing Address" value={`${carrierInfo.mailingAddress}, ${carrierInfo.mailingCity}, ${carrierInfo.mailingState} ${carrierInfo.mailingZip}`} />}
                    <PreviewItem label="Main Contact" value={carrierInfo.mainContact} />
                    <PreviewItem label="Email" value={carrierInfo.email} />
                    <PreviewItem label="Office Phone" value={carrierInfo.officePhone} />
                    <PreviewItem label="Fax" value={carrierInfo.fax} />
                    <PreviewItem label="Cell Phone" value={carrierInfo.cellPhone} />
                    <PreviewItem label="Emergency Contact" value={carrierInfo.emergencyContact} />
                    <PreviewItem label="Emergency Phone" value={carrierInfo.emergencyPhone} />
                    <PreviewItem label="MC #" value={carrierInfo.mcNumber} />
                    <PreviewItem label="DOT #" value={carrierInfo.dotNumber} />
                    <PreviewItem label="EIN #" value={carrierInfo.einNumber} />
                    <PreviewItem label="SCAC Code" value={carrierInfo.scacCode} />
                    <PreviewItem label="TWIC Certified" value={carrierInfo.twicCertified} />
                    <PreviewItem label="Hazmat Certified" value={carrierInfo.hazmatCertified} />
                </div>
            </PreviewSection>

            <PreviewSection title="Equipment Information" onEdit={() => onEdit(1)}>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2">
                    <PreviewItem label="# of Trucks" value={equipmentInfo.numTrucks} />
                    <PreviewItem label="Company Drivers" value={equipmentInfo.companyDrivers} />
                    <PreviewItem label="Owner Operators" value={equipmentInfo.ownerOperators} />
                    <PreviewItem label="Team Drivers" value={equipmentInfo.teamDrivers} />
                    <PreviewItem label="# of Trailers" value={equipmentInfo.numTrailers} />
                    <PreviewItem label="Van Trailers" value={equipmentInfo.vanTrailers} />
                    <PreviewItem label="Reefer Trailers" value={equipmentInfo.reeferTrailers} />
                    <PreviewItem label="Flatbed Trailers" value={equipmentInfo.flatbedTrailers} />
                    <PreviewItem label="Tanker Trailers" value={equipmentInfo.tankerTrailers} />
                    <PreviewItem label="Other Trailer Types" value={equipmentInfo.otherTrailerTypes} />
                    <PreviewItem label="Van Sizes" value={equipmentInfo.vanSizes} />
                    <PreviewItem label="Reefer Sizes" value={equipmentInfo.reeferSizes} />
                    <PreviewItem label="Flatbed Sizes" value={equipmentInfo.flatbedSizes} />
                    <PreviewItem label="Tanker Sizes" value={equipmentInfo.tankerSizes} />
                </div>
                <PreviewTable title="Tractors" data={equipmentInfo.tractors} columns={[{ key: 'year', label: 'Year' }, { key: 'makeModel', label: 'Make/Model' }, { key: 'truckNum', label: 'Truck #' }, { key: 'vin', label: 'VIN #' }]} />
                <PreviewTable title="Trailers" data={equipmentInfo.trailers} columns={[{ key: 'year', label: 'Year' }, { key: 'makeModel', label: 'Make/Model' }, { key: 'trailerNum', label: 'Trailer #' }, { key: 'vin', label: 'VIN #' }]} />
                <PreviewTable title="Drivers" data={equipmentInfo.drivers} columns={[{ key: 'truckNum', label: 'Truck #' }, { key: 'trailerNum', label: 'Trailer #' }, { key: 'trailerType', label: 'Trailer Type' }, { key: 'maxWeight', label: 'Max Weight' }, { key: 'driverName', label: 'Driver Name' }, { key: 'driverCell', label: 'Driver Cell' }]} />
                <div className="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2 mt-4">
                  <PreviewItem label="Drivers can make load decisions" value={equipmentInfo.driversCanMakeDecisions} />
                  <PreviewItem label="Drivers need copy of confirmation" value={equipmentInfo.driversNeedCopy} />
                </div>
                <PreviewItem label="Equipment Description" value={equipmentInfo.equipmentDescription} />
            </PreviewSection>

            <PreviewSection title="Area of Operation" onEdit={() => onEdit(2)}>
                 <div className="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2">
                    <PreviewItem label="Canada Provinces" value={operationInfo.canadaProvinces} />
                    <PreviewItem label="Mexico" value={operationInfo.mexico} />
                    <PreviewItem label="US States" value={operationInfo.states} />
                    <PreviewItem label="Min. Rate / Mile" value={operationInfo.minRatePerMile} />
                    <PreviewItem label="Max Picks" value={operationInfo.maxPicks} />
                    <PreviewItem label="Max Drops" value={operationInfo.maxDrops} />
                    <PreviewItem label="$ Per Pick/Drop" value={operationInfo.perPickDrop} />
                    <PreviewItem label="Driver Touch" value={operationInfo.driverTouch} />
                    <PreviewItem label="Driver Touch Comments" value={operationInfo.driverTouchComments} />
                 </div>
            </PreviewSection>

             <PreviewSection title="Factoring Information" onEdit={() => onEdit(3)}>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2">
                    <PreviewItem label="Factoring Company" value={factoringInfo.factoringCompany} />
                    <PreviewItem label="Main Contact" value={factoringInfo.mainContact} />
                    <PreviewItem label="Phone" value={factoringInfo.phone} />
                    <PreviewItem label="Fax" value={factoringInfo.fax} />
                    <PreviewItem label="Website URL" value={factoringInfo.websiteUrl} />
                    {factoringInfo.address && <PreviewItem label="Address" value={`${factoringInfo.address}, ${factoringInfo.city}, ${factoringInfo.state} ${factoringInfo.zip}`} />}
                </div>
            </PreviewSection>

             <PreviewSection title="Insurance Information" onEdit={() => onEdit(4)}>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2">
                    <PreviewItem label="Insurance Agency" value={insuranceInfo.insuranceAgency} />
                    <PreviewItem label="Main Contact" value={insuranceInfo.mainContact} />
                    <PreviewItem label="Phone" value={insuranceInfo.phone} />
                    <PreviewItem label="Fax" value={insuranceInfo.fax} />
                    <PreviewItem label="Email" value={insuranceInfo.email} />
                    {insuranceInfo.address && <PreviewItem label="Address" value={`${insuranceInfo.address}, ${insuranceInfo.city}, ${insuranceInfo.state} ${insuranceInfo.zip}`} />}
                </div>
                <PreviewItem label="Company Description" value={insuranceInfo.companyDescription} />
            </PreviewSection>
        </div>
        </ScrollArea>
    );
}

export default function CarrierProfileForm() {
  const [currentStep, setCurrentStep] = useState(0);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [formCompleted, setFormCompleted] = useState(false);
  const auth = useAuth();
  const firestore = useFirestore();

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
        driversCanMakeDecisions: undefined, driversNeedCopy: undefined,
        equipmentDescription: '',
      },
      operationInfo: {
        states: [], canadaProvinces: '', mexico: '', minRatePerMile: '',
        maxPicks: '', maxDrops: '', perPickDrop: '', driverTouch: undefined, 
        driverTouchComments: '',
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

  const { handleSubmit, trigger, formState } = methods;

  const processForm = async (data: FullFormValues) => {
    setIsSubmitting(true);
    console.log('Form data submitted:', data);

    if (!auth.currentUser) {
        console.error("User is not authenticated. Cannot save carrier profile.");
        setIsSubmitting(false);
        // Optionally, show a toast or message to the user
        return;
    }

    try {
        const carrierId = auth.currentUser.uid;
        const batch = writeBatch(firestore);

        // Main Carrier document
        const carrierRef = doc(firestore, 'carriers', carrierId);
        batch.set(carrierRef, data.carrierInfo);

        // Sub-collections
        const { equipmentInfo, operationInfo, factoringInfo, insuranceInfo } = data;

        // Equipment
        if (Object.keys(equipmentInfo).some(key => equipmentInfo[key as keyof typeof equipmentInfo])) {
            const equipmentRef = doc(firestore, `carriers/${carrierId}/equipment`, 'main');
            const { tractors, trailers, drivers, ...mainEquipmentData } = equipmentInfo;
            batch.set(equipmentRef, mainEquipmentData);

            tractors?.forEach(tractor => {
                const tractorRef = doc(firestore, `carriers/${carrierId}/tractors`, tractor.vin || crypto.randomUUID());
                batch.set(tractorRef, tractor);
            });
            trailers?.forEach(trailer => {
                const trailerRef = doc(firestore, `carriers/${carrierId}/trailers`, trailer.vin || crypto.randomUUID());
                batch.set(trailerRef, trailer);
            });
            drivers?.forEach(driver => {
                const driverRef = doc(firestore, `carriers/${carrierId}/drivers`, driver.driverCell || crypto.randomUUID());
                batch.set(driverRef, driver);
            });
        }
        
        // Operation
        if (Object.keys(operationInfo).some(key => operationInfo[key as keyof typeof operationInfo])) {
            const operationRef = doc(firestore, `carriers/${carrierId}/operations`, 'main');
            batch.set(operationRef, operationInfo);
        }
        
        // Factoring
        if (Object.keys(factoringInfo).some(key => factoringInfo[key as keyof typeof factoringInfo])) {
            const factoringRef = doc(firestore, `carriers/${carrierId}/factoring`, 'main');
            batch.set(factoringRef, factoringInfo);
        }
        
        // Insurance
        if (Object.keys(insuranceInfo).some(key => insuranceInfo[key as keyof typeof insuranceInfo])) {
            const insuranceRef = doc(firestore, `carriers/${carrierId}/insurance`, 'main');
            batch.set(insuranceRef, insuranceInfo);
        }
        
        await batch.commit();
        console.log("Carrier profile successfully saved to Firestore.");
        setFormCompleted(true);
    } catch (error) {
        console.error("Error saving carrier profile to Firestore: ", error);
        // Optionally, show an error toast to the user
    } finally {
        setIsSubmitting(false);
    }
  };
  
  const nextStep = async () => {
    const currentStepConfig = steps[currentStep];
    const stepId = currentStepConfig.id;
    let isValid = true;
    
    // Only validate if there's a schema for the current step
    if ('schema' in currentStepConfig) {
      isValid = await trigger(stepId as FieldPath<FullFormValues>, { shouldFocus: true });
    }
    
    if (isValid && currentStep < steps.length - 1) {
      setCurrentStep(step => step + 1);
    }
  };

  const prevStep = () => {
    if (currentStep > 0) {
      setCurrentStep(step => step - 1);
    }
  };

  const goToStep = (step: number) => {
    if (step >= 0 && step < steps.length -1) {
        setCurrentStep(step);
    }
  }


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
              Thank you for completing your profile. Your information has been securely saved to our database. We will review your information and get in touch shortly.
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
              <ol className="flex items-center w-full max-w-4xl">
                {steps.map((step, index) => (
                  <li key={step.id} className={cn(
                      "relative flex w-full items-center",
                      index < steps.length - 1 ? "after:content-[''] after:w-full after:h-1 after:border-b after:border-4 after:inline-block" : "",
                      index < currentStep ? "after:border-primary" : "after:border-muted",
                  )}>
                    <button type="button" onClick={() => goToStep(index)} className="flex flex-col items-center" disabled={index >= currentStep && !formState.isValid}>
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
                    </button>
                  </li>
                ))}
              </ol>
            </div>
            <Separator />
        </CardHeader>
      <CardContent className="p-4 md:p-6">
        <FormProvider {...methods}>
          <form onSubmit={handleSubmit(processForm)} className="space-y-8">
            <div className={currentStep === 0 ? 'block' : 'hidden'}><CarrierInfoForm /></div>
            <div className={currentStep === 1 ? 'block' : 'hidden'}><EquipmentInfoForm /></div>
            <div className={currentStep === 2 ? 'block' : 'hidden'}><OperationInfoForm /></div>
            <div className={currentStep === 3 ? 'block' : 'hidden'}><FactoringInfoForm /></div>
            <div className={currentStep === 4 ? 'block' : 'hidden'}><InsuranceInfoForm /></div>
            <div className={currentStep === 5 ? 'block' : 'hidden'}><PreviewForm onEdit={goToStep} /></div>
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

    

    