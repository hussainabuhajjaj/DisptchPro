
"use client";

import { useActionState } from "react";
import { useFormStatus } from "react-dom";
import { generateFaqAction } from "@/app/actions";
import { Button } from "./ui/button";
import { Textarea } from "./ui/textarea";
import { Card, CardContent, CardHeader, CardTitle } from "./ui/card";
import { WandSparkles, LoaderCircle } from "lucide-react";

const initialState = {
  message: "",
};

function SubmitButton() {
  const { pending } = useFormStatus();
  return (
    <Button type="submit" disabled={pending} className="w-full sm:w-auto">
      {pending ? (
        <>
          <LoaderCircle className="mr-2 h-4 w-4 animate-spin" />
          Generating...
        </>
      ) : (
        <>
          <WandSparkles className="mr-2 h-4 w-4" />
          Generate Answers
        </>
      )}
    </Button>
  );
}

export default function FaqGeneratorClient() {
  const [state, formAction] = useActionState(generateFaqAction, initialState);

  return (
    <>
      <Card className="shadow-lg">
        <CardContent className="p-6">
          <form action={formAction} className="space-y-4">
            <Textarea
              name="details"
              placeholder="Enter details about your dispatching service and common client questions. For example: &#10;We are a 24/7 dispatch service for owner-operators in the US.&#10;What are your fees?&#10;How do you find loads?&#10;What kind of support do you offer?"
              rows={8}
              required
              className="bg-background"
            />
            <SubmitButton />
          </form>
        </CardContent>
      </Card>
      {state.message && (
        <div className="mt-6">
          <Card className="bg-secondary/50">
            <CardHeader>
              <CardTitle className="text-lg">Generated FAQ</CardTitle>
            </CardHeader>
            <CardContent>
              <pre className="whitespace-pre-wrap text-sm text-foreground/80 font-sans">
                {state.message}
              </pre>
            </CardContent>
          </Card>
        </div>
      )}
    </>
  );
}
