'use client';

import {useState, useEffect, useRef} from 'react';
import {useActionState} from 'react';
import {useFormStatus} from 'react-dom';
import {Button} from '@/components/ui/button';
import {
  Card,
  CardContent,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import {Input} from '@/components/ui/input';
import {ScrollArea} from '@/components/ui/scroll-area';
import {cn} from '@/lib/utils';
import {
  Bot,
  LoaderCircle,
  MessageSquare,
  Send,
  User,
  X,
} from 'lucide-react';
import {chatAction} from '@/app/chatbot-actions';

interface Message {
  role: 'user' | 'model';
  content: string;
}

const initialState = {};

function SubmitButton() {
  const {pending} = useFormStatus();
  return (
    <Button type="submit" size="icon" disabled={pending}>
      {pending ? (
        <LoaderCircle className="animate-spin" />
      ) : (
        <Send />
      )}
      <span className="sr-only">Send message</span>
    </Button>
  );
}

export default function Chatbot() {
  const [isOpen, setIsOpen] = useState(false);
  const [messages, setMessages] = useState<Message[]>([
    {
      role: 'model',
      content:
        "Hello! I'm Dispatch Pro's AI assistant. How can I help you today?",
    },
  ]);
  const [state, formAction] = useActionState(chatAction, initialState);
  const scrollAreaRef = useRef<HTMLDivElement>(null);
  const formRef = useRef<HTMLFormElement>(null);

  useEffect(() => {
    if (state.response) {
      setMessages(prev => [...prev, {role: 'model', content: state.response!}]);
    }
    if (state.error) {
      setMessages(prev => [
        ...prev,
        {role: 'model', content: state.error!},
      ]);
    }
  }, [state]);

  useEffect(() => {
    if (scrollAreaRef.current) {
      scrollAreaRef.current.scrollTo({
        top: scrollAreaRef.current.scrollHeight,
        behavior: 'smooth',
      });
    }
  }, [messages]);

  const handleFormSubmit = (formData: FormData) => {
    const message = formData.get('message') as string;
    if (message.trim()) {
      setMessages(prev => [...prev, {role: 'user', content: message}]);
      formAction(formData);
      formRef.current?.reset();
    }
  };

  return (
    <>
      <div className="fixed bottom-6 right-6 z-50">
        <Button
          size="icon"
          className="rounded-full w-14 h-14 shadow-lg"
          onClick={() => setIsOpen(!isOpen)}
        >
          {isOpen ? <X /> : <MessageSquare />}
          <span className="sr-only">Toggle Chatbot</span>
        </Button>
      </div>

      {isOpen && (
        <Card className="fixed bottom-24 right-6 z-50 w-full max-w-sm shadow-xl flex flex-col">
          <CardHeader className="flex flex-row items-center justify-between">
            <div className="flex items-center gap-3">
              <Bot className="text-primary" />
              <CardTitle className="text-lg">Dispatch Pro Assistant</CardTitle>
            </div>
          </CardHeader>
          <ScrollArea className="h-96 flex-1" ref={scrollAreaRef}>
            <CardContent className="p-4 space-y-4">
              {messages.map((msg, index) => (
                <div
                  key={index}
                  className={cn(
                    'flex gap-3 text-sm',
                    msg.role === 'user' ? 'justify-end' : 'justify-start'
                  )}
                >
                  {msg.role === 'model' && (
                    <div className="p-2 bg-primary/10 rounded-full h-fit">
                      <Bot className="w-4 h-4 text-primary" />
                    </div>
                  )}
                  <div
                    className={cn(
                      'rounded-lg px-4 py-2 max-w-[80%]',
                      msg.role === 'user'
                        ? 'bg-primary text-primary-foreground'
                        : 'bg-muted'
                    )}
                  >
                    <p className="whitespace-pre-wrap">{msg.content}</p>
                  </div>
                  {msg.role === 'user' && (
                    <div className="p-2 bg-muted rounded-full h-fit">
                      <User className="w-4 h-4" />
                    </div>
                  )}
                </div>
              ))}
            </CardContent>
          </ScrollArea>
          <CardFooter className="p-4 border-t">
            <form
              ref={formRef}
              action={handleFormSubmit}
              className="flex w-full items-center gap-2"
            >
              <Input
                name="message"
                placeholder="Type your message..."
                autoComplete="off"
              />
              <input
                type="hidden"
                name="history"
                value={JSON.stringify(messages)}
              />
              <SubmitButton />
            </form>
          </CardFooter>
        </Card>
      )}
    </>
  );
}
