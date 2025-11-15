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
import {Label} from '@/components/ui/label';
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

interface UserDetails {
  name: string;
  email: string;
  company: string;
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

function PreChatForm({onSubmit}: {onSubmit: (details: UserDetails) => void}) {
  const [details, setDetails] = useState({name: '', email: '', company: ''});

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (details.name && details.email) {
      onSubmit(details);
    }
  };

  return (
    <CardContent className="p-4">
      <form onSubmit={handleSubmit} className="space-y-4">
        <CardHeader className="p-0 mb-4">
          <CardTitle className="text-lg">Welcome!</CardTitle>
          <p className="text-sm text-muted-foreground">
            Please fill in your details to start the chat.
          </p>
        </CardHeader>
        <div className="space-y-2">
          <Label htmlFor="name">Name</Label>
          <Input
            id="name"
            value={details.name}
            onChange={e => setDetails({...details, name: e.target.value})}
            required
            placeholder="John Doe"
          />
        </div>
        <div className="space-y-2">
          <Label htmlFor="email">Email</Label>
          <Input
            id="email"
            type="email"
            value={details.email}
            onChange={e => setDetails({...details, email: e.target.value})}
            required
            placeholder="john.doe@example.com"
          />
        </div>
        <div className="space-y-2">
          <Label htmlFor="company">Company Name (Optional)</Label>
          <Input
            id="company"
            value={details.company}
            onChange={e => setDetails({...details, company: e.target.value})}
            placeholder="Acme Inc."
          />
        </div>
        <Button type="submit" className="w-full">
          Start Chat
        </Button>
      </form>
    </CardContent>
  );
}


export default function Chatbot() {
  const [isOpen, setIsOpen] = useState(false);
  const [userDetails, setUserDetails] = useState<UserDetails | null>(null);
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
    if (message.trim() && userDetails) {
      setMessages(prev => [...prev, {role: 'user', content: message}]);
      const fullFormData = new FormData();
      fullFormData.append('message', message);
      fullFormData.append('history', JSON.stringify(messages));
      fullFormData.append('userDetails', JSON.stringify(userDetails));
      formAction(fullFormData);
      formRef.current?.reset();
    }
  };
  
  const handlePreChatSubmit = (details: UserDetails) => {
    setUserDetails(details);
    console.log("User details collected:", details);
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
          {!userDetails ? (
             <PreChatForm onSubmit={handlePreChatSubmit} />
          ) : (
            <>
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
            </>
          )}
        </Card>
      )}
    </>
  );
}
