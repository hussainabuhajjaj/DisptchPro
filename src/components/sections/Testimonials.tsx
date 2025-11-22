import Image from "next/image";
import {
  Carousel,
  CarouselContent,
  CarouselItem,
  CarouselNext,
  CarouselPrevious,
} from "@/components/ui/carousel";
import { Card, CardContent } from "@/components/ui/card";
import { Avatar, AvatarImage, AvatarFallback } from "@/components/ui/avatar";
import { Quote } from "lucide-react";
import { PlaceHolderImages } from "@/lib/placeholder-images";

const testimonials = [
  {
    quote:
      "H&A Dispatch transformed my operations. My revenue is up 30%, and I have more time to focus on driving. Their team is professional and always available.",
    name: "John D., Owner-Operator",
    avatarId: "testimonial-avatar-1",
  },
  {
    quote:
      "Finding profitable loads used to be a nightmare. With H&A Dispatch, I get consistent, high-paying work without the stress. Highly recommended!",
    name: "Maria S., Small Fleet Owner",
    avatarId: "testimonial-avatar-2",
  },
  {
    quote:
      "The paperwork handling alone is worth it. They are efficient, reliable, and truly understand the trucking business. A five-star service.",
    name: "David L., Independent Trucker",
    avatarId: "testimonial-avatar-3",
  },
];

export default function Testimonials() {
  const getImage = (id: string) => PlaceHolderImages.find((img) => img.id === id);

  return (
    <section id="testimonials" className="w-full py-16 md:py-24">
      <div className="container mx-auto px-4 md:px-6">
        <div className="mx-auto max-w-3xl text-center mb-12">
          <h2 className="text-3xl md:text-4xl font-bold tracking-tighter">
            What Our Clients Say
          </h2>
          <p className="mt-4 text-lg text-muted-foreground">
            Real stories from truckers and fleet owners who trust H&A Dispatch.
          </p>
        </div>
        <Carousel
          opts={{
            align: "start",
            loop: true,
          }}
          className="w-full max-w-4xl mx-auto"
        >
          <CarouselContent>
            {testimonials.map((testimonial, index) => (
              <CarouselItem key={index} className="md:basis-1/2 lg:basis-1/3">
                <div className="p-1 h-full">
                  <Card className="h-full flex flex-col justify-between shadow-sm transition-all duration-300 hover:shadow-xl hover:-translate-y-2">
                    <CardContent className="p-6 flex flex-col gap-6">
                      <Quote className="h-8 w-8 text-primary/50" />
                      <p className="text-muted-foreground text-base">
                        "{testimonial.quote}"
                      </p>
                      <div className="flex items-center gap-4 pt-4 border-t">
                        <Avatar>
                          <AvatarImage
                            src={getImage(testimonial.avatarId)?.imageUrl}
                            alt={testimonial.name}
                            data-ai-hint={getImage(testimonial.avatarId)?.imageHint}
                          />
                          <AvatarFallback>{testimonial.name.charAt(0)}</AvatarFallback>
                        </Avatar>
                        <div>
                          <p className="font-semibold text-sm text-foreground">
                            {testimonial.name.split(',')[0]}
                          </p>
                           <p className="text-xs text-muted-foreground">
                            {testimonial.name.split(',')[1]}
                           </p>
                        </div>
                      </div>
                    </CardContent>
                  </Card>
                </div>
              </CarouselItem>
            ))}
          </CarouselContent>
          <CarouselPrevious className="hidden sm:flex" />
          <CarouselNext className="hidden sm:flex" />
        </Carousel>
      </div>
    </section>
  );
}
